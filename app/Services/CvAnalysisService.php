<?php
// ==========================================
// app/Services/CvAnalysisService.php
// ==========================================
namespace App\Services;

use App\Models\User;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Log;

/**
 * Professional CV/Resume Analysis Service
 * Uses: smalot/pdfparser for PDF extraction
 *       Custom NLP for skill extraction
 */
class CvAnalysisService
{
    private array $technicalSkills = [
        'php', 'laravel', 'python', 'django', 'javascript', 'typescript', 'react', 'vue', 'angular',
        'node.js', 'express', 'java', 'spring', 'kotlin', 'swift', 'c#', '.net', 'c++', 'rust', 'go',
        'html', 'css', 'sass', 'tailwind', 'bootstrap', 'mysql', 'postgresql', 'mongodb', 'redis',
        'elasticsearch', 'docker', 'kubernetes', 'aws', 'azure', 'gcp', 'git', 'linux', 'nginx',
        'graphql', 'rest', 'api', 'microservices', 'ai', 'machine learning', 'tensorflow', 'pytorch',
        'data science', 'sql', 'power bi', 'tableau', 'excel', 'word', 'photoshop', 'figma', 'sketch',
        'flutter', 'react native', 'android', 'ios', 'blockchain', 'devops', 'ci/cd', 'jenkins',
        'selenium', 'testing', 'agile', 'scrum', 'jira', 'confluence',
    ];

    private array $softSkills = [
        'leadership', 'communication', 'teamwork', 'problem solving', 'critical thinking',
        'creativity', 'adaptability', 'time management', 'project management', 'analytical',
        'detail oriented', 'self motivated', 'collaborative', 'presentation',
        // Arabic soft skills
        'قيادة', 'تواصل', 'فريق', 'حل المشكلات', 'إبداع', 'إدارة الوقت', 'إدارة المشاريع',
    ];

    private array $languages = [
        'english', 'arabic', 'french', 'german', 'spanish', 'chinese', 'turkish',
        'الإنجليزية', 'العربية', 'الفرنسية', 'الألمانية', 'الإسبانية',
    ];

    private array $educationKeywords = [
        'bachelor', 'master', 'phd', 'doctorate', 'diploma', 'degree', 'university', 'college',
        'بكالوريوس', 'ماجستير', 'دكتوراه', 'دبلوم', 'جامعة', 'كلية', 'شهادة',
    ];

    public function analyzePdf(string $filePath): array
    {
        try {
            $text = $this->extractText($filePath);
            return $this->analyzeText($text);
        } catch (\Throwable $e) {
            Log::error('CV Analysis failed: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'raw_text' => ''];
        }
    }

    public function analyzeUser(User $user): void
    {
        if (!$user->cv_path) return;

        $fullPath = storage_path('app/private/' . $user->cv_path);
        if (!file_exists($fullPath)) return;

        $analysis = $this->analyzePdf($fullPath);
        $user->update([
            'cv_analyzed' => $analysis,
            'skills'      => $analysis['technical_skills'] ?? [],
        ]);
    }

    private function extractText(string $filePath): string
    {
        $fullPath = storage_path('app/private/' . $filePath);

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            return $this->extractFromPdf($fullPath);
        }

        if (in_array($extension, ['doc', 'docx'])) {
            return $this->extractFromWord($fullPath);
        }

        throw new \InvalidArgumentException("Unsupported file type: {$extension}");
    }

    private function extractFromPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf    = $parser->parseFile($path);
        $text   = $pdf->getText();

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function extractFromWord(string $path): string
    {
        // Use phpoffice/phpword for DOCX
        if (class_exists('\PhpOffice\PhpWord\IOFactory')) {
            $phpWord  = \PhpOffice\PhpWord\IOFactory::load($path);
            $sections = $phpWord->getSections();
            $text = '';
            foreach ($sections as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . ' ';
                    }
                }
            }
            return trim($text);
        }

        // Fallback: read raw XML from docx
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $xml  = $zip->getFromName('word/document.xml');
            $zip->close();
            $text = strip_tags(str_replace(['</w:p>', '</w:r>'], ["\n", ' '], $xml));
            return trim($text);
        }

        return '';
    }

    private function analyzeText(string $text): array
    {
        $textLower = mb_strtolower($text);

        return [
            'raw_text'          => substr($text, 0, 500) . '...',
            'word_count'        => str_word_count($text),
            'technical_skills'  => $this->extractSkills($textLower, $this->technicalSkills),
            'soft_skills'       => $this->extractSkills($textLower, $this->softSkills),
            'languages'         => $this->extractSkills($textLower, $this->languages),
            'education'         => $this->extractEducation($text),
            'experience_years'  => $this->estimateExperience($text),
            'contact_info'      => $this->extractContactInfo($text),
            'score'             => $this->calculateScore($textLower),
            'analyzed_at'       => now()->toIsoString(),
        ];
    }

    private function extractSkills(string $text, array $skillList): array
    {
        $found = [];
        foreach ($skillList as $skill) {
            if (str_contains($text, mb_strtolower($skill))) {
                $found[] = $skill;
            }
        }
        return array_values(array_unique($found));
    }

    private function extractEducation(string $text): array
    {
        $education = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $lineLower = mb_strtolower($line);
            foreach ($this->educationKeywords as $keyword) {
                if (str_contains($lineLower, mb_strtolower($keyword))) {
                    $clean = trim($line);
                    if (strlen($clean) > 10 && strlen($clean) < 200) {
                        $education[] = $clean;
                    }
                    break;
                }
            }
        }

        return array_values(array_unique(array_slice($education, 0, 5)));
    }

    private function estimateExperience(string $text): int
    {
        // Look for patterns like "5 years", "3+ years", "سنوات"
        preg_match_all('/(\d+)\+?\s*(?:years?|yrs?|سنوات?|عام|أعوام)/i', $text, $matches);
        if (!empty($matches[1])) {
            return (int) max($matches[1]);
        }

        // Count distinct year mentions (e.g., 2020, 2021, 2022 = ~2 years)
        preg_match_all('/\b(20\d{2}|19\d{2})\b/', $text, $years);
        if (!empty($years[1])) {
            $uniqueYears = array_unique($years[1]);
            return max(1, count($uniqueYears) - 1);
        }

        return 0;
    }

    private function extractContactInfo(string $text): array
    {
        $contact = [];

        // Email
        preg_match('/[\w._%+\-]+@[\w.\-]+\.[a-zA-Z]{2,}/', $text, $email);
        if ($email) $contact['email'] = $email[0];

        // Phone
        preg_match('/[\+]?[\d\s\-\(\)]{8,15}/', $text, $phone);
        if ($phone) $contact['phone'] = trim($phone[0]);

        // LinkedIn
        preg_match('/linkedin\.com\/in\/[\w\-]+/', $text, $linkedin);
        if ($linkedin) $contact['linkedin'] = $linkedin[0];

        // GitHub
        preg_match('/github\.com\/[\w\-]+/', $text, $github);
        if ($github) $contact['github'] = $github[0];

        return $contact;
    }

    private function calculateScore(string $text): int
    {
        $score = 0;

        // Has contact info
        if (str_contains($text, '@')) $score += 10;
        if (preg_match('/\d{7,}/', $text)) $score += 5;

        // Has education
        foreach ($this->educationKeywords as $kw) {
            if (str_contains($text, mb_strtolower($kw))) { $score += 10; break; }
        }

        // Technical skills count
        $techCount = count($this->extractSkills($text, $this->technicalSkills));
        $score += min(30, $techCount * 3);

        // Soft skills count
        $softCount = count($this->extractSkills($text, $this->softSkills));
        $score += min(15, $softCount * 3);

        // Languages
        $langCount = count($this->extractSkills($text, $this->languages));
        $score += min(10, $langCount * 5);

        // Length (comprehensive CV)
        $wordCount = str_word_count($text);
        if ($wordCount > 200) $score += 5;
        if ($wordCount > 400) $score += 5;
        if ($wordCount > 600) $score += 5;

        return min(100, $score);
    }
}

// ==========================================
// app/Services/JobRecommendationService.php
// ==========================================
namespace App\Services;

use App\Models\User;
use App\Models\Job;

class JobRecommendationService
{
    public function recommend(User $user, int $limit = 10)
    {
        $userSkills   = $user->skills ?? [];
        $userLocation = $user->location;
        $userTypes    = $user->preferred_job_types ?? [];
        $userLocations= $user->preferred_locations ?? [];

        // Score-based recommendation
        $jobs = Job::with(['company', 'category'])
            ->active()
            ->where('company_id', '!=', optional($user->company)->id)
            ->get()
            ->map(function ($job) use ($user, $userSkills, $userLocation, $userTypes, $userLocations) {
                $score = 0;

                // Skill match
                $jobSkills = $job->skills ?? [];
                if (!empty($jobSkills) && !empty($userSkills)) {
                    $matchedSkills = array_intersect(
                        array_map('mb_strtolower', $jobSkills),
                        array_map('mb_strtolower', $userSkills)
                    );
                    $score += count($matchedSkills) * 15;
                }

                // Location preference
                if (!empty($userLocations) && in_array($job->location, $userLocations)) {
                    $score += 20;
                } elseif ($userLocation && str_contains(mb_strtolower($job->location), mb_strtolower($userLocation))) {
                    $score += 10;
                }

                // Job type preference
                if (!empty($userTypes) && in_array($job->type, $userTypes)) {
                    $score += 15;
                }

                // Experience match
                if ($user->experience_level === $job->experience_level) {
                    $score += 20;
                }

                // Remote preference
                if ($job->is_remote) $score += 5;

                // Salary match
                if ($user->expected_salary && $job->salary_max >= $user->expected_salary) {
                    $score += 10;
                }

                // Recency bonus
                $daysOld = $job->created_at->diffInDays();
                if ($daysOld <= 3)       $score += 15;
                elseif ($daysOld <= 7)   $score += 10;
                elseif ($daysOld <= 14)  $score += 5;

                // Featured bonus
                if ($job->is_featured) $score += 5;

                $job->recommendation_score = $score;
                return $job;
            })
            ->filter(fn($job) => $job->recommendation_score > 0)
            ->sortByDesc('recommendation_score')
            ->take($limit)
            ->values();

        // If not enough recommendations, pad with latest jobs
        if ($jobs->count() < $limit) {
            $existingIds = $jobs->pluck('id')->toArray();
            $extra = Job::with(['company', 'category'])
                ->active()
                ->whereNotIn('id', $existingIds)
                ->latest()
                ->limit($limit - $jobs->count())
                ->get()
                ->map(function ($job) {
                    $job->recommendation_score = 0;
                    return $job;
                });
            $jobs = $jobs->merge($extra);
        }

        return $jobs;
    }
}