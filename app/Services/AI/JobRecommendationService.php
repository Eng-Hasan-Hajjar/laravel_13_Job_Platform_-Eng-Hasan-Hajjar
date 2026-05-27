<?php
// ═══════════════════════════════════════════════════════════════════
// app/Services/AI/JobRecommendationService.php
//
// الخوارزميات المستخدمة:
// ────────────────────────────────────────────────────────────────
// 1. TF-IDF (Term Frequency – Inverse Document Frequency)
//    → يحوّل المهارات إلى أوزان رقمية:
//      المهارة النادرة في سوق العمل تحصل على وزن أعلى
//
// 2. Cosine Similarity
//    → يقيس التشابه بين ملف المستخدم والوظيفة
//      النتيجة بين 0.0 (لا تشابه) و 1.0 (تطابق تام)
//
// 3. Weighted Multi-Factor Scoring
//    ┌─────────────────┬────────┐
//    │ العامل          │ الوزن  │
//    ├─────────────────┼────────┤
//    │ المهارات        │  40%   │
//    │ مستوى الخبرة   │  20%   │
//    │ الموقع الجغرافي│  15%   │
//    │ الراتب          │  10%   │
//    │ نوع الوظيفة    │  10%   │
//    │ حداثة النشر     │   5%   │
//    └─────────────────┴────────┘
// ═══════════════════════════════════════════════════════════════════

namespace App\Services\AI;

use App\Models\{User, Job};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobRecommendationService
{
    // ── أوزان عوامل التقييم
    private const WEIGHTS = [
        'skills'     => 0.40,
        'experience' => 0.20,
        'location'   => 0.15,
        'salary'     => 0.10,
        'job_type'   => 0.10,
        'recency'    => 0.05,
    ];

    // ── الحد الأدنى لقبول الوظيفة كتوصية (10%)
    private const MIN_THRESHOLD = 0.10;

    // ════════════════════════════════════════════════════════════════
    //  الدالة الرئيسية
    // ════════════════════════════════════════════════════════════════
    public function recommend(User $user, int $limit = 20, bool $useAI = true): Collection
    {
        if (!$useAI) {
            return $this->basicLatest($user, $limit);
        }

        // بناء متجه المستخدم مرة واحدة
        $userVector = $this->buildUserVector($user);

        $jobs = Job::with(['company', 'category'])
            ->active()
            ->whereDoesntHave('applications', fn($q) => $q->where('user_id', $user->id))
            ->get();

        $scored = $jobs->map(function (Job $job) use ($user, $userVector) {
                $score = $this->score($user, $job, $userVector);

                $job->ai_score        = round($score * 100, 1);
                $job->ai_reasons      = $this->explain($user, $job);
                $job->skill_match_pct = $this->skillMatchPct($user, $job);

                return $job;
            })
            ->filter(fn($job) => $job->ai_score >= self::MIN_THRESHOLD * 100)
            ->sortByDesc('ai_score')
            ->take($limit)
            ->values();

        // تكملة بالحديثة إذا لم تكفِ النتائج
        if ($scored->count() < $limit) {
            $existingIds = $scored->pluck('id');
            $extra = Job::with(['company', 'category'])
                ->active()
                ->whereNotIn('id', $existingIds)
                ->whereDoesntHave('applications', fn($q) => $q->where('user_id', $user->id))
                ->latest()
                ->take($limit - $scored->count())
                ->get()
                ->map(fn($j) => tap($j, function ($j) {
                    $j->ai_score = 0;
                    $j->ai_reasons = [];
                    $j->skill_match_pct = 0;
                }));
            $scored = $scored->merge($extra);
        }

        return $scored;
    }

    // ════════════════════════════════════════════════════════════════
    //  حساب النقاط الإجمالية لوظيفة
    // ════════════════════════════════════════════════════════════════
    private function score(User $user, Job $job, array $userVector): float
    {
        $factors = [
            'skills'     => $this->skillsScore($user, $job, $userVector),
            'experience' => $this->experienceScore($user, $job),
            'location'   => $this->locationScore($user, $job),
            'salary'     => $this->salaryScore($user, $job),
            'job_type'   => $this->jobTypeScore($user, $job),
            'recency'    => $this->recencyScore($job),
        ];

        $total = 0;
        foreach (self::WEIGHTS as $key => $weight) {
            $total += ($factors[$key] ?? 0) * $weight;
        }

        // مكافآت إضافية
        if ($job->is_featured)           $total = min(1, $total + 0.05);
        if ($job->company->is_verified)  $total = min(1, $total + 0.03);

        return round($total, 4);
    }

    // ════════════════════════════════════════════════════════════════
    //  1. نقاط المهارات — TF-IDF + Cosine Similarity
    // ════════════════════════════════════════════════════════════════
    private function skillsScore(User $user, Job $job, array $userVector): float
    {
        $jobSkills = array_map('mb_strtolower', $job->skills ?? []);

        if (empty($jobSkills)) return 0.5;     // وظيفة مفتوحة للجميع

        $jobVector = $this->buildSkillVector($jobSkills);

        // Cosine Similarity بين ملف المستخدم والوظيفة
        $cosine = $this->cosine($userVector, $jobVector);

        // نسبة التطابق المباشر (ratio)
        $userSkillsLow = array_map('mb_strtolower', $user->skills ?? []);
        $matched       = count(array_intersect($userSkillsLow, $jobSkills));
        $ratio         = $matched / max(count($jobSkills), 1);

        // متوسط موزون: 70% Cosine، 30% نسبة تطابق مباشر
        return ($cosine * 0.7) + ($ratio * 0.3);
    }

    // ════════════════════════════════════════════════════════════════
    //  2. نقاط مستوى الخبرة — Ordinal Distance
    // ════════════════════════════════════════════════════════════════
    private function experienceScore(User $user, Job $job): float
    {
        $map = ['entry' => 0, 'junior' => 1, 'mid' => 2, 'senior' => 3, 'lead' => 4];

        $u = $map[$user->experience_level ?? 'mid'] ?? 2;
        $j = $map[$job->experience_level  ?? 'mid'] ?? 2;

        return match(abs($u - $j)) {
            0 => 1.00,
            1 => 0.75,
            2 => 0.40,
            3 => 0.15,
            default => 0.0,
        };
    }

    // ════════════════════════════════════════════════════════════════
    //  3. نقاط الموقع الجغرافي
    // ════════════════════════════════════════════════════════════════
    private function locationScore(User $user, Job $job): float
    {
        if ($job->is_remote) return 1.0;     // عن بُعد = مناسب للجميع

        $prefLoc  = array_map('mb_strtolower', $user->preferred_locations ?? []);
        $userLoc  = mb_strtolower($user->location ?? '');
        $jobLoc   = mb_strtolower($job->location  ?? '');

        foreach ($prefLoc as $pref) {
            if (str_contains($jobLoc, $pref) || str_contains($pref, $jobLoc))
                return 1.0;
        }

        if ($userLoc && str_contains($jobLoc, $userLoc)) return 0.85;

        // نفس الدولة
        if ($this->country($userLoc) === $this->country($jobLoc) && $this->country($userLoc))
            return 0.50;

        return 0.10;
    }

    // ════════════════════════════════════════════════════════════════
    //  4. نقاط الراتب
    // ════════════════════════════════════════════════════════════════
    private function salaryScore(User $user, Job $job): float
    {
        $expected = $user->expected_salary ?? 0;
        if (!$expected) return 0.5;

        $mid = (($job->salary_min ?? 0) + ($job->salary_max ?? 0)) / 2;
        if (!$mid) return 0.5;

        $ratio = $mid / $expected;

        if ($ratio >= 1.20) return 1.00;
        if ($ratio >= 1.00) return 0.95;
        if ($ratio >= 0.85) return 0.75;
        if ($ratio >= 0.70) return 0.50;
        if ($ratio >= 0.50) return 0.25;
        return 0.0;
    }

    // ════════════════════════════════════════════════════════════════
    //  5. نقاط نوع الوظيفة
    // ════════════════════════════════════════════════════════════════
    private function jobTypeScore(User $user, Job $job): float
    {
        $prefs = $user->preferred_job_types ?? [];
        if (empty($prefs)) return 0.5;
        return in_array($job->type, $prefs) ? 1.0 : 0.1;
    }

    // ════════════════════════════════════════════════════════════════
    //  6. نقاط الحداثة — Exponential Decay
    // ════════════════════════════════════════════════════════════════
    private function recencyScore(Job $job): float
    {
        $days = $job->created_at->diffInDays(now());

        if ($days <= 1)  return 1.00;
        if ($days <= 3)  return 0.90;
        if ($days <= 7)  return 0.75;
        if ($days <= 14) return 0.55;
        if ($days <= 30) return 0.35;
        if ($days <= 60) return 0.15;
        return 0.05;
    }

    // ════════════════════════════════════════════════════════════════
    //  Cosine Similarity
    //  cos(θ) = (A·B) / (|A| × |B|)
    // ════════════════════════════════════════════════════════════════
    private function cosine(array $vecA, array $vecB): float
    {
        if (empty($vecA) || empty($vecB)) return 0.0;

        $keys = array_unique(array_merge(array_keys($vecA), array_keys($vecB)));
        $dot = $magA = $magB = 0.0;

        foreach ($keys as $k) {
            $a = $vecA[$k] ?? 0.0;
            $b = $vecB[$k] ?? 0.0;
            $dot  += $a * $b;
            $magA += $a * $a;
            $magB += $b * $b;
        }

        $mag = sqrt($magA) * sqrt($magB);
        return $mag > 0 ? min(1.0, $dot / $mag) : 0.0;
    }

    // ════════════════════════════════════════════════════════════════
    //  بناء TF-IDF Vector للمستخدم
    // ════════════════════════════════════════════════════════════════
    private function buildUserVector(User $user): array
    {
        $skills = array_map('mb_strtolower', $user->skills ?? []);

        // إضافة مهارات CV المحللة
        if ($user->cv_analyzed) {
            $cvTech  = array_map('mb_strtolower', $user->cv_analyzed['technical_skills'] ?? []);
            $cvSoft  = array_map('mb_strtolower', $user->cv_analyzed['soft_skills']      ?? []);
            $skills  = array_unique(array_merge($skills, $cvTech, $cvSoft));
        }

        return $this->buildSkillVector($skills);
    }

    // ════════════════════════════════════════════════════════════════
    //  بناء TF-IDF Vector للمهارات
    // ════════════════════════════════════════════════════════════════
    private function buildSkillVector(array $skills): array
    {
        if (empty($skills)) return [];

        $totalDocs = Cache::remember('total_jobs_count', 3600, fn() => max(Job::count(), 1));
        $vector    = [];

        foreach ($skills as $skill) {
            $skill = mb_strtolower(trim($skill));
            if (!$skill) continue;

            // TF = 1 (كل مهارة تُذكر مرة)
            $tf = 1.0;

            // IDF = log(N / df) + 1   — المهارات النادرة أهم
            $df  = Cache::remember("skill_df_{$skill}", 3600, fn() =>
                max(Job::whereJsonContains('skills', $skill)->count(), 1)
            );
            $idf = log($totalDocs / $df) + 1;

            $vector[$skill] = $tf * $idf;
        }

        // L2 Normalization
        $mag = sqrt(array_sum(array_map(fn($v) => $v * $v, $vector)));
        if ($mag > 0) {
            foreach ($vector as &$val) $val /= $mag;
        }

        return $vector;
    }

    // ════════════════════════════════════════════════════════════════
    //  شرح أسباب التوصية (لعرضها في الواجهة)
    // ════════════════════════════════════════════════════════════════
    public function explain(User $user, Job $job): array
    {
        $reasons = [];
        $userS   = array_map('mb_strtolower', $user->skills ?? []);
        $jobS    = array_map('mb_strtolower', $job->skills  ?? []);
        $matched = array_intersect($userS, $jobS);

        if (count($matched)) {
            $reasons[] = ['icon'=>'💻', 'text' => count($matched).' matched skills: '.implode(', ', array_slice($matched,0,3))];
        }
        if ($job->experience_level === $user->experience_level) {
            $reasons[] = ['icon'=>'📊', 'text' => 'Matches your experience level'];
        }
        if ($job->is_remote) {
            $reasons[] = ['icon'=>'🌍', 'text' => 'Remote work available'];
        } elseif ($user->location && str_contains(mb_strtolower($job->location), mb_strtolower($user->location))) {
            $reasons[] = ['icon'=>'📍', 'text' => 'Near your location'];
        }
        if ($user->expected_salary && $job->salary_max >= $user->expected_salary) {
            $reasons[] = ['icon'=>'💰', 'text' => 'Salary meets your expectation'];
        }
        if (!empty($user->preferred_job_types) && in_array($job->type, $user->preferred_job_types)) {
            $reasons[] = ['icon'=>'⏱', 'text' => 'Your preferred job type'];
        }

        return $reasons;
    }

    // ════════════════════════════════════════════════════════════════
    //  نسبة تطابق المهارات للعرض
    // ════════════════════════════════════════════════════════════════
    public function skillMatchPct(User $user, Job $job): int
    {
        $jobS  = array_map('mb_strtolower', $job->skills  ?? []);
        if (empty($jobS)) return 0;

        $userS = array_map('mb_strtolower', $user->skills ?? []);
        if ($user->cv_analyzed) {
            $cv    = array_map('mb_strtolower', $user->cv_analyzed['technical_skills'] ?? []);
            $userS = array_unique(array_merge($userS, $cv));
        }

        return (int) min(100, round(count(array_intersect($userS, $jobS)) / count($jobS) * 100));
    }

    // ── Fallback بدون AI
    private function basicLatest(User $user, int $limit): Collection
    {
        return Job::with(['company','category'])->active()
            ->whereDoesntHave('applications', fn($q) => $q->where('user_id', $user->id))
            ->latest()->take($limit)->get()
            ->map(fn($j) => tap($j, fn($j) => $j->ai_score = $j->ai_reasons = $j->skill_match_pct = null));
    }

    private function country(string $loc): string
    {
        $parts = explode(',', $loc);
        return mb_strtolower(trim(end($parts)));
    }
}
