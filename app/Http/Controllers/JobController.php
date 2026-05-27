<?php
// app/Http/Controllers/JobController.php
// أضف هذه الدوال إلى الـ Controller الموجود

namespace App\Http\Controllers;

use App\Models\{Job, Category};
use App\Services\AI\JobRecommendationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(private JobRecommendationService $ai) {}

    // ════════════════════════════════════════════════════════════════
    //  صفحة الوظائف المقترحة
    // ════════════════════════════════════════════════════════════════
    public function recommended(Request $request)
    {
        $user  = auth()->user();
        $useAI = $request->boolean('ai', true);  // افتراضياً: AI مفعّل

        $jobs = $this->ai->recommend($user, 20, $useAI);

        $profileStats = [
            'skills_count'    => count($user->skills ?? []),
            'cv_analyzed'     => !empty($user->cv_analyzed),
            'cv_score'        => $user->cv_analyzed['score'] ?? 0,
            'location_set'    => !empty($user->location),
            'prefs_set'       => !empty($user->preferred_job_types),
            'completeness'    => $this->completeness($user),
        ];

        return view('jobs.recommended', compact('jobs', 'useAI', 'profileStats'));
    }

    // ════════════════════════════════════════════════════════════════
    //  API: تحميل المزيد (AJAX / Infinite Scroll)
    // ════════════════════════════════════════════════════════════════
    public function recommendedApi(Request $request)
    {
        $user   = auth()->user();
        $useAI  = $request->boolean('ai', true);
        $page   = max(1, $request->integer('page', 1));
        $perPage = 6;

        $all  = $this->ai->recommend($user, 60, $useAI);
        $page_items = $all->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'jobs'     => $page_items->map(fn($j) => [
                'id'          => $j->id,
                'title'       => $j->title,
                'company'     => $j->company->name,
                'logo'        => $j->company->logo ? asset('storage/'.$j->company->logo) : null,
                'location'    => $j->location,
                'type'        => $j->type,
                'ai_score'    => $j->ai_score ?? 0,
                'ai_reasons'  => $j->ai_reasons ?? [],
                'skill_match' => $j->skill_match_pct ?? 0,
                'salary_min'  => $j->salary_min,
                'salary_max'  => $j->salary_max,
                'is_remote'   => $j->is_remote,
                'url'         => route('jobs.show', $j->slug),
                'applied'     => false,
            ]),
            'has_more' => $all->count() > $page * $perPage,
            'total'    => $all->count(),
            'page'     => $page,
        ]);
    }

    // ─── helper: نسبة اكتمال الملف الشخصي ─────────────────────────
    private function completeness(object $user): int
    {
        $fields = ['name','email','phone','location','bio','cv_path','skills','experience_level','avatar'];
        $filled = count(array_filter($fields, fn($f) => !empty($user->$f)));
        return (int) round(($filled / count($fields)) * 100);
    }
}
