<?php
// ═══════════════════════════════════════════════════════════════════
// app/Services/AI/CandidateRankingService.php
//
// الخوارزميات المستخدمة:
// ────────────────────────────────────────────────────────────────
// 1. Skill Match (Cosine Similarity)      — 35%
// 2. CV Quality Score (من تحليل PDF)     — 20%
// 3. Experience Match (Ordinal Distance)  — 20%
// 4. Cover Letter Quality (NLP Heuristic)— 15%
// 5. Salary Alignment                     — 10%
// ═══════════════════════════════════════════════════════════════════

namespace App\Services\AI;

use App\Models\{Job, JobApplication, User};
use Illuminate\Support\Collection;

class CandidateRankingService
{
    private const WEIGHTS = [
        'skills'       => 0.35,
        'cv_quality'   => 0.20,
        'experience'   => 0.20,
        'cover_letter' => 0.15,
        'salary'       => 0.10,
    ];

    // ════════════════════════════════════════════════════════════════
    //  الدالة الرئيسية: ترتيب المتقدمين لوظيفة معينة
    // ════════════════════════════════════════════════════════════════
    public function rank(Job $job, bool $useAI = true): Collection
    {
        $applications = JobApplication::with('user')
            ->where('job_id', $job->id)
            ->get();

        if (!$useAI) {
            return $applications->sortByDesc('created_at')->values();
        }

        return $applications->map(function (JobApplication $app) use ($job) {
            $user   = $app->user;
            $scores = $this->allScores($job, $user, $app);

            $total  = array_sum(array_map(
                fn($key, $w) => ($scores[$key] ?? 0) * $w,
                array_keys(self::WEIGHTS),
                self::WEIGHTS
            ));

            $app->ai_score        = round($total * 100, 1);
            $app->ai_scores       = $scores;
            $app->ai_breakdown    = $this->breakdown($job, $user, $scores);
            $app->ai_summary      = $this->summary($app->ai_score);
            $app->skill_match_pct = $this->skillMatchPct($job, $user);

            return $app;
        })
        ->sortByDesc('ai_score')
        ->values();
    }

    // ════════════════════════════════════════════════════════════════
    //  حساب جميع نقاط المتقدم
    // ════════════════════════════════════════════════════════════════
    private function allScores(Job $job, User $user, JobApplication $app): array
    {
        return [
            'skills'       => $this->skillsScore($job, $user),
            'cv_quality'   => $this->cvQualityScore($user),
            'experience'   => $this->experienceScore($job, $user),
            'cover_letter' => $this->coverLetterScore($app->cover_letter, $job),
            'salary'       => $this->salaryScore($job, $app->expected_salary),
        ];
    }

    // ════════════════════════════════════════════════════════════════
    //  1. نقاط المهارات — Cosine Similarity
    // ════════════════════════════════════════════════════════════════
    private function skillsScore(Job $job, User $user): float
    {
        $jobS = array_map('mb_strtolower', $job->skills ?? []);
        if (empty($jobS)) return 0.5;

        $userS = array_map('mb_strtolower', $user->skills ?? []);
        if ($user->cv_analyzed) {
            $cv    = array_map('mb_strtolower', $user->cv_analyzed['technical_skills'] ?? []);
            $soft  = array_map('mb_strtolower', $user->cv_analyzed['soft_skills']      ?? []);
            $userS = array_unique(array_merge($userS, $cv, $soft));
        }

        if (empty($userS)) return 0.0;

        return $this->cosine($this->vec($jobS), $this->vec($userS));
    }

    // ════════════════════════════════════════════════════════════════
    //  2. جودة السيرة الذاتية (من نتيجة تحليل PDF: 0–100)
    // ════════════════════════════════════════════════════════════════
    private function cvQualityScore(User $user): float
    {
        if (!$user->cv_analyzed) return 0.30;    // لم يُحلَّل بعد

        $score      = ($user->cv_analyzed['score'] ?? 0) / 100;
        $contact    = $user->cv_analyzed['contact_info'] ?? [];

        // مكافأة على كمال بيانات التواصل
        if (!empty($contact['email']))    $score = min(1, $score + 0.05);
        if (!empty($contact['linkedin'])) $score = min(1, $score + 0.03);
        if (!empty($contact['github']))   $score = min(1, $score + 0.02);

        return $score;
    }

    // ════════════════════════════════════════════════════════════════
    //  3. مستوى الخبرة — Ordinal Distance
    // ════════════════════════════════════════════════════════════════
    private function experienceScore(Job $job, User $user): float
    {
        $map = ['entry'=>0, 'junior'=>1, 'mid'=>2, 'senior'=>3, 'lead'=>4];

        $u = $map[$user->experience_level ?? 'mid'] ?? 2;
        $j = $map[$job->experience_level  ?? 'mid'] ?? 2;

        // إذا كان المتقدم أعلى من المطلوب: مقبول لكن بوزن أقل
        if ($u > $j) return max(0.60, 1 - ($u - $j) * 0.10);

        return match($j - $u) {
            0 => 1.00,
            1 => 0.70,
            2 => 0.30,
            default => 0.05,
        };
    }

    // ════════════════════════════════════════════════════════════════
    //  4. جودة خطاب التقديم — NLP Heuristic
    //
    //  المعايير المقيَّمة:
    //  ─ الطول المناسب (150–600 كلمة)
    //  ─ ذكر المسمى الوظيفي
    //  ─ مفردات مهنية إيجابية
    //  ─ عدم التكرار
    //  ─ ذكر مهارات الوظيفة
    // ════════════════════════════════════════════════════════════════
    private function coverLetterScore(string $letter, Job $job): float
    {
        $score = 0.0;
        $text  = mb_strtolower(trim($letter));
        $words = str_word_count($letter);

        // ─ الطول
        if ($words >= 150 && $words <= 600) $score += 0.30;
        elseif ($words >= 80)               $score += 0.15;
        elseif ($words >= 40)               $score += 0.05;

        // ─ ذكر المسمى الوظيفي
        $titleWords   = array_filter(explode(' ', mb_strtolower($job->title)), fn($w) => strlen($w) > 3);
        $titleHits    = count(array_filter($titleWords, fn($w) => str_contains($text, $w)));
        if ($titleHits >= 2) $score += 0.20;
        elseif ($titleHits)  $score += 0.10;

        // ─ كلمات مهنية إيجابية
        $keywords = ['experience','passionate','contribute','achieve','team','skills',
                     'background','opportunity','develop','خبرة','متحمس','أساهم','فريق'];
        $hits = count(array_filter($keywords, fn($k) => str_contains($text, $k)));
        $score += min(0.25, $hits * 0.04);

        // ─ تنويع الجمل (لا نسخ)
        $sentences  = array_filter(explode('.', $letter), fn($s) => trim($s));
        $uniq       = array_unique(array_map('trim', $sentences));
        $score     += count($sentences) ? (count($uniq) / count($sentences)) * 0.15 : 0;

        // ─ ذكر مهارات الوظيفة
        $jobSkills  = array_map('mb_strtolower', $job->skills ?? []);
        $mentioned  = count(array_filter($jobSkills, fn($s) => str_contains($text, $s)));
        $score     += min(0.10, $mentioned * 0.03);

        return min(1.0, $score);
    }

    // ════════════════════════════════════════════════════════════════
    //  5. توافق الراتب
    // ════════════════════════════════════════════════════════════════
    private function salaryScore(Job $job, ?float $expected): float
    {
        if (!$expected) return 0.5;

        $min = $job->salary_min ?? 0;
        $max = $job->salary_max ?? 0;
        if (!$min && !$max) return 0.5;

        if ($expected >= $min && $expected <= $max) return 1.0;   // داخل النطاق
        if ($expected < $min)  return 0.85;                         // أقل من الحد = جيد للشركة
        $ratio = $expected / max($max, 1);
        if ($ratio <= 1.15) return 0.60;
        if ($ratio <= 1.30) return 0.30;
        return 0.05;
    }

    // ════════════════════════════════════════════════════════════════
    //  تفصيل النقاط للعرض في الواجهة
    // ════════════════════════════════════════════════════════════════
    public function breakdown(Job $job, User $user, array $scores): array
    {
        $jobS  = array_map('mb_strtolower', $job->skills ?? []);
        $userS = array_map('mb_strtolower', $user->skills ?? []);
        if ($user->cv_analyzed) {
            $cv    = array_map('mb_strtolower', $user->cv_analyzed['technical_skills'] ?? []);
            $userS = array_unique(array_merge($userS, $cv));
        }
        $matched = array_values(array_intersect($userS, $jobS));
        $missing = array_values(array_diff($jobS, $userS));

        return [
            'skills' => [
                'score'   => round($scores['skills'] * 100),
                'matched' => array_slice($matched, 0, 6),
                'missing' => array_slice($missing, 0, 4),
            ],
            'cv_quality' => [
                'score'    => round($scores['cv_quality'] * 100),
                'cv_score' => $user->cv_analyzed['score'] ?? 0,
                'analyzed' => !empty($user->cv_analyzed),
            ],
            'experience' => [
                'score'      => round($scores['experience'] * 100),
                'user_level' => $user->experience_level ?? '—',
                'job_level'  => $job->experience_level,
            ],
            'cover_letter' => [
                'score' => round($scores['cover_letter'] * 100),
            ],
            'salary' => [
                'score'    => round($scores['salary'] * 100),
                'expected' => $user->expected_salary,
                'job_min'  => $job->salary_min,
                'job_max'  => $job->salary_max,
            ],
        ];
    }

    // ════════════════════════════════════════════════════════════════
    //  ملخص نصي للنتيجة
    // ════════════════════════════════════════════════════════════════
    public function summary(float $score): string
    {
        if ($score >= 85) return '🌟 Excellent — Highly Recommended';
        if ($score >= 70) return '✅ Strong match — Well qualified';
        if ($score >= 55) return '👍 Good match — Meets most requirements';
        if ($score >= 40) return '⚠️ Partial match — Some gaps';
        return '❌ Weak match — Significant skill gaps';
    }

    // نسبة تطابق المهارات
    public function skillMatchPct(Job $job, User $user): int
    {
        $jobS = array_map('mb_strtolower', $job->skills ?? []);
        if (empty($jobS)) return 0;

        $userS = array_map('mb_strtolower', $user->skills ?? []);
        if ($user->cv_analyzed) {
            $cv    = array_map('mb_strtolower', $user->cv_analyzed['technical_skills'] ?? []);
            $userS = array_unique(array_merge($userS, $cv));
        }

        return (int) min(100, round(count(array_intersect($userS, $jobS)) / count($jobS) * 100));
    }

    // ────── helpers ──────────────────────────────────────────────────

    private function cosine(array $a, array $b): float
    {
        if (empty($a) || empty($b)) return 0.0;

        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
        $dot = $mA = $mB = 0.0;

        foreach ($keys as $k) {
            $av = $a[$k] ?? 0.0;
            $bv = $b[$k] ?? 0.0;
            $dot += $av * $bv;
            $mA  += $av * $av;
            $mB  += $bv * $bv;
        }

        $mag = sqrt($mA) * sqrt($mB);
        return $mag > 0 ? min(1.0, $dot / $mag) : 0.0;
    }

    /** TF-IDF vector مبسّط مع L2 normalization */
    private function vec(array $terms): array
    {
        $v = [];
        foreach ($terms as $t) {
            $t = mb_strtolower(trim($t));
            if ($t) $v[$t] = ($v[$t] ?? 0) + 1;
        }
        $mag = sqrt(array_sum(array_map(fn($x) => $x * $x, $v)));
        if ($mag > 0) foreach ($v as &$val) $val /= $mag;
        return $v;
    }
}
