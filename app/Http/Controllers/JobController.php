<?php
// app/Http/Controllers/JobController.php

namespace App\Http\Controllers;

use App\Models\{Job, Category, Company};
use App\Services\AI\JobRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function __construct(private JobRecommendationService $ai) {}

    /**
     * Display a listing of jobs (public page)
     */
    public function index(Request $request)
    {
        // Build the query
        $query = Job::query()
            ->active()
            ->with(['company', 'category'])
            ->when($request->q, function($q, $search) {
                return $q->where(function($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->location, function($q, $location) {
                return $q->where(function($sub) use ($location) {
                    $sub->where('location', 'like', "%{$location}%")
                        ->orWhere('is_remote', true);
                });
            })
            ->when($request->category, function($q, $category) {
                return $q->whereHas('category', fn($cq) => $cq->where('slug', $category));
            })
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->experience, fn($q, $exp) => $q->where('experience_level', $exp))
            ->when($request->min_salary, fn($q, $min) => $q->where('salary_max', '>=', $min))
            ->when($request->max_salary, fn($q, $max) => $q->where('salary_min', '<=', $max));

        // Order by
        $orderBy = $request->order ?? 'latest';
        switch($orderBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'salary_high':
                $query->orderByDesc('salary_max');
                break;
            case 'salary_low':
                $query->orderBy('salary_min');
                break;
            default:
                $query->latest();
        }

        $jobs = $query->paginate(15)->withQueryString();

        // Get filter options
        $categories = Category::active()->withCount('jobs')->get();
        $locations = Job::active()->distinct()->pluck('location')->filter()->values();
        $jobTypes = ['full-time', 'part-time', 'freelance', 'remote', 'internship'];
        $experienceLevels = ['entry', 'junior', 'mid', 'senior', 'lead'];

        return view('jobs.index', compact('jobs', 'categories', 'locations', 'jobTypes', 'experienceLevels'));
    }

    /**
     * Display job details
     */
    public function show($slug)
    {
        $job = Job::where('slug', $slug)
            ->active()
            ->with(['company', 'company.reviews', 'category'])
            ->firstOrFail();

        // Increment view count
        $job->incrementViews();

        // Get related jobs
        $relatedJobs = Job::active()
            ->where('category_id', $job->category_id)
            ->where('id', '!=', $job->id)
            ->limit(4)
            ->get();

        // Check if user has applied (if logged in)
        $hasApplied = false;
        $hasSaved = false;

        if (auth()->check() && auth()->user()->isUser()) {
            $hasApplied = auth()->user()->hasAppliedTo($job);
            $hasSaved = auth()->user()->hasSaved($job);
        }

        return view('jobs.show', compact('job', 'relatedJobs', 'hasApplied', 'hasSaved'));
    }

    /**
     * Apply for a job
     */
    public function apply(Request $request, Job $job)
    {
        $user = auth()->user();

        // Check if user is a job seeker
        if (!$user->isUser()) {
            return back()->with('error', __('messages.only_job_seekers_can_apply'));
        }

        // Check if already applied
        if ($user->hasAppliedTo($job)) {
            return back()->with('error', __('messages.already_applied'));
        }

        // Check if deadline has passed
        if ($job->isExpired()) {
            return back()->with('error', __('messages.job_expired'));
        }

        // Create application
        $application = $job->applications()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'cover_letter' => $request->cover_letter,
        ]);

        // Attach CV if exists
        if ($user->cv_path) {
            $application->update(['cv_path' => $user->cv_path]);
        }

        // Notify company
        $job->company->user->notify(new \App\Notifications\NewJobApplication($application));

        return back()->with('success', __('messages.application_submitted'));
    }

    /**
     * Save a job for later
     */
    public function save(Job $job)
    {
        $user = auth()->user();

        if (!$user->isUser()) {
            return response()->json(['error' => __('messages.unauthorized')], 403);
        }

        if ($user->hasSaved($job)) {
            $user->savedJobs()->detach($job->id);
            $saved = false;
            $message = __('messages.job_removed_from_saved');
        } else {
            $user->savedJobs()->attach($job->id);
            $saved = true;
            $message = __('messages.job_saved');
        }

        if (request()->ajax()) {
            return response()->json(['saved' => $saved, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Display recommended jobs (AI-based)
     */
    public function recommended(Request $request)
    {
        $user = auth()->user();
        $useAI = $request->boolean('ai', true);

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

    /**
     * API endpoint for loading more recommended jobs (AJAX)
     */
    public function recommendedApi(Request $request)
    {
        $user = auth()->user();
        $useAI = $request->boolean('ai', true);
        $page = max(1, $request->integer('page', 1));
        $perPage = 6;

        $all = $this->ai->recommend($user, 60, $useAI);
        $pageItems = $all->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'jobs' => $pageItems->map(fn($j) => [
                'id'          => $j->id,
                'title'       => $j->title,
                'company'     => $j->company->name,
                'company_slug' => $j->company->slug,
                'logo'        => $j->company->logo ? asset('storage/' . $j->company->logo) : null,
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

    /**
     * Helper: Calculate profile completeness percentage
     */
    private function completeness(object $user): int
    {
        $fields = ['name', 'email', 'phone', 'location', 'bio', 'cv_path', 'skills', 'experience_level', 'avatar'];
        $filled = count(array_filter($fields, fn($f) => !empty($user->$f)));
        return (int) round(($filled / count($fields)) * 100);
    }
}