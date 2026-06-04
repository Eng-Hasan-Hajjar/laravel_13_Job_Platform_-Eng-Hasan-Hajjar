<?php

namespace App\Http\Controllers;

use App\Models\{Job, Category};
use App\Services\AI\JobRecommendationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(private JobRecommendationService $ai) {}

    // ══════════════════════════════════════════════════════════════
    //  قائمة الوظائف (صفحة البحث)
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $query = Job::query()
            ->active()
            ->with(['company', 'category'])
            ->when($request->q, fn($q, $s) => $q->where(fn($sub) => $sub
                ->where('title',       'like', "%{$s}%")
                ->orWhere('description','like', "%{$s}%")
                ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%{$s}%"))
            ))
            ->when($request->location, fn($q, $loc) => $q->where(fn($sub) => $sub
                ->where('location', 'like', "%{$loc}%")
                ->orWhere('is_remote', true)
            ))
            ->when($request->category, fn($q, $cat) => $q
                ->whereHas('category', fn($c) => $c->where('slug', $cat))
            )
            ->when($request->experience, fn($q, $exp) => $q->where('experience_level', $exp))
            ->when($request->salary_min, fn($q, $min) => $q->where('salary_max', '>=', $min))
            ->when($request->salary_max, fn($q, $max) => $q->where('salary_min', '<=', $max))
            ->when($request->posted,     fn($q, $d)   => $q->where('created_at', '>=', now()->subDays($d)));

        // فلتر نوع الوظيفة (checkbox متعدد)
        if ($request->filled('type')) {
            $types = (array) $request->type;
            $query->whereIn('type', $types);
        }

        // الترتيب
        match ($request->sort ?? 'latest') {
            'salary_high' => $query->orderByDesc('salary_max'),
            'salary_low'  => $query->orderBy('salary_min'),
            default       => $query->latest(),
        };

        $jobs       = $query->paginate(15)->withQueryString();
        $categories = Category::active()->withCount('jobs')->get();
        $locations  = Job::active()->distinct()->pluck('location')->filter()->values();

        return view('jobs.index', compact('jobs', 'categories', 'locations'));
    }

    // ══════════════════════════════════════════════════════════════
    //  تفاصيل وظيفة واحدة
    // ══════════════════════════════════════════════════════════════
    public function show(string $slug)
    {
        $job = Job::where('slug', $slug)
            ->active()
            ->with(['company.reviews', 'category'])
            ->firstOrFail();

        $job->incrementViews();

        // ← اسم المتغير يطابق الـ View: $similarJobs
        $similarJobs = Job::active()
            ->where('category_id', $job->category_id)
            ->where('id', '!=', $job->id)
            ->with('company')
            ->latest()
            ->limit(4)
            ->get();

        $hasApplied = false;
        $hasSaved   = false;

        if (auth()->check() && auth()->user()->isUser()) {
            $hasApplied = auth()->user()->hasAppliedTo($job);
            $hasSaved   = auth()->user()->hasSaved($job);
        }

        return view('jobs.show', compact('job', 'similarJobs', 'hasApplied', 'hasSaved'));
    }

    // ══════════════════════════════════════════════════════════════
    //  التقديم على وظيفة
    // ══════════════════════════════════════════════════════════════
    public function apply(Request $request, Job $job)
    {
        $user = auth()->user();

        if (!$user->isUser()) {
            return back()->with('error', __('messages.only_seekers_can_apply'));
        }

        if ($user->hasAppliedTo($job)) {
            return back()->with('error', __('messages.already_applied'));
        }

        if ($job->deadline && $job->deadline->isPast()) {
            return back()->with('error', __('messages.application_closed'));
        }

        $request->validate([
            'cover_letter'    => 'required|string|min:30|max:3000',
            'cv_file'         => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability'    => 'nullable|in:immediately,two_weeks,one_month,negotiable',
        ]);

        // تحديد مسار الـ CV
        $cvPath = $user->cv_path; // الافتراضي: CV الموجود

        if ($request->hasFile('cv_file')) {
            $cvPath = $request->file('cv_file')
                ->store('cvs/' . $user->id, 'private');
        }

        if (!$cvPath) {
            return back()->withErrors(['cv_file' => __('messages.cv_required')]);
        }

        $application = $job->applications()->create([
            'user_id'         => $user->id,
            'status'          => 'pending',
            'cover_letter'    => $request->cover_letter,
            'cv_path'         => $cvPath,
            'expected_salary' => $request->expected_salary,
            'availability'    => $request->availability ?? 'negotiable',
        ]);

        // إشعار الشركة
        $job->company->user->notify(
            new \App\Notifications\ApplicationReceived($application)
        );

        // إشعار المتقدم
        $user->notify(
            new \App\Notifications\ApplicationSubmitted($application)
        );

        return back()->with('success', __('messages.application_submitted'));
    }

    // ══════════════════════════════════════════════════════════════
    //  حفظ / إلغاء حفظ وظيفة
    // ══════════════════════════════════════════════════════════════
    public function save(Job $job)
    {
        $user = auth()->user();

        if (!$user || !$user->isUser()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->hasSaved($job)) {
            $user->savedJobs()->detach($job->id);
            $saved = false;
        } else {
            $user->savedJobs()->attach($job->id);
            $saved = true;
        }

        return response()->json([
            'saved'   => $saved,
            'message' => $saved
                ? __('messages.job_saved')
                : __('messages.job_unsaved'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  الوظائف المقترحة (AI)
    // ══════════════════════════════════════════════════════════════
    public function recommended(Request $request)
    {
        $user  = auth()->user();
        $useAI = $request->boolean('ai', true);

        $jobs = $this->ai->recommend($user, 20, $useAI);

        $profileStats = [
            'skills_count' => count($user->skills ?? []),
            'cv_analyzed'  => !empty($user->cv_analyzed),
            'cv_score'     => $user->cv_analyzed['score'] ?? 0,
            'location_set' => !empty($user->location),
            'prefs_set'    => !empty($user->preferred_job_types),
            'completeness' => $this->completeness($user),
        ];

        return view('jobs.recommended', compact('jobs', 'useAI', 'profileStats'));
    }

    // API للـ infinite scroll
    public function recommendedApi(Request $request)
    {
        $user    = auth()->user();
        $useAI   = $request->boolean('ai', true);
        $page    = max(1, $request->integer('page', 1));
        $perPage = 6;

        $all   = $this->ai->recommend($user, 60, $useAI);
        $items = $all->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'jobs'     => $items->map(fn($j) => [
                'id'          => $j->id,
                'title'       => $j->title,
                'company'     => $j->company->name,
                'logo'        => $j->company->logo ? asset('storage/' . $j->company->logo) : null,
                'location'    => $j->location,
                'type'        => $j->type,
                'ai_score'    => $j->ai_score    ?? 0,
                'ai_reasons'  => $j->ai_reasons  ?? [],
                'skill_match' => $j->skill_match_pct ?? 0,
                'salary_min'  => $j->salary_min,
                'salary_max'  => $j->salary_max,
                'is_remote'   => $j->is_remote,
                'url'         => route('jobs.show', $j->slug),
            ]),
            'has_more' => $all->count() > $page * $perPage,
            'total'    => $all->count(),
            'page'     => $page,
        ]);
    }

    // ── helper ────────────────────────────────────────────────────
    private function completeness(object $user): int
    {
        $fields = ['name','email','phone','location','bio','cv_path','skills','experience_level','avatar'];
        $filled = count(array_filter($fields, fn($f) => !empty($user->$f)));
        return (int) round(($filled / count($fields)) * 100);
    }
}