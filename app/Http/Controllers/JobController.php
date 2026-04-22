<?php

// ==========================================
// app/Http/Controllers/JobController.php
// ==========================================
namespace App\Http\Controllers;
 
use App\Models\Job;
use App\Models\Category;
use App\Models\JobApplication;
use App\Services\CvAnalysisService;
use App\Services\JobRecommendationService;
use App\Notifications\ApplicationReceived;
use App\Notifications\NewJobPosted;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
 
class JobController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::active()->withCount(['jobs' => fn($q) => $q->active()])->get();
 
        $jobs = Job::with(['company', 'category'])
            ->active()
            ->search($request->q)
            ->ofType($request->type)
            ->inLocation($request->location)
            ->withSalaryBetween($request->salary_min, $request->salary_max)
            ->postedWithin($request->posted)
            ->when($request->category, fn($q) => $q->whereIn('category_id', $request->category))
            ->when($request->experience, fn($q) => $q->whereIn('experience_level', $request->experience))
            ->when($request->sort === 'salary_high', fn($q) => $q->orderByDesc('salary_max'))
            ->when($request->sort === 'salary_low', fn($q) => $q->orderBy('salary_min'))
            ->when(!in_array($request->sort, ['salary_high','salary_low']), fn($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();
 
        if ($request->format === 'json') {
            return response()->json([
                'html' => view('jobs._results', compact('jobs'))->render(),
                'total' => $jobs->total(),
            ]);
        }
 
        return view('jobs.index', compact('jobs', 'categories'));
    }
 
    public function show(Job $job)
    {
        abort_if(!$job->is_active, 404);
        $job->incrementViews();
 
        $hasApplied = auth()->check()
            ? auth()->user()->hasAppliedTo($job)
            : false;
 
        $similarJobs = Job::with('company')
            ->active()
            ->where('id', '!=', $job->id)
            ->where(fn($q) => $q
                ->where('category_id', $job->category_id)
                ->orWhere('company_id', $job->company_id))
            ->limit(4)
            ->get();
 
        $job->load(['company', 'category']);
        $job->company->loadCount('reviews');
        $job->loadCount('applications');
 
        return view('jobs.show', compact('job', 'hasApplied', 'similarJobs'));
    }
 
    public function apply(Request $request, Job $job)
    {
        abort_if(!auth()->user()->isUser(), 403);
        abort_if($job->isExpired(), 422, __('messages.application_closed'));
 
        if (auth()->user()->hasAppliedTo($job)) {
            return back()->with('warning', __('messages.already_applied'));
        }
 
        $request->validate([
            'cover_letter'    => 'required|min:100|max:5000',
            'cv_file'         => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability'    => 'required|in:immediately,two_weeks,one_month,negotiable',
            'cv_option'       => 'nullable|in:existing,new',
        ]);
 
        // Handle CV
        $cvPath = auth()->user()->cv_path;
        if ($request->cv_option === 'new' || !$cvPath) {
            if ($request->hasFile('cv_file')) {
                $cvPath = $request->file('cv_file')->store('cvs/' . auth()->id(), 'private');
                // Update user CV
                auth()->user()->update(['cv_path' => $cvPath]);
                // Analyze CV in background
                dispatch(new \App\Jobs\AnalyzeCvJob(auth()->user()));
            }
        }
 
        abort_if(!$cvPath, 422, __('messages.cv_required'));
 
        $application = JobApplication::create([
            'user_id'         => auth()->id(),
            'job_id'          => $job->id,
            'cover_letter'    => $request->cover_letter,
            'cv_path'         => $cvPath,
            'expected_salary' => $request->expected_salary,
            'availability'    => $request->availability,
            'status'          => 'pending',
        ]);
 
        $job->increment('applications_count');
 
        // Send notification to company
        $job->company->user->notify(new ApplicationReceived($application));
 
        // Send confirmation to applicant
        auth()->user()->notify(new \App\Notifications\ApplicationSubmitted($application));
 
        return redirect()->route('user.applications')
                         ->with('success', __('messages.application_submitted'));
    }
 
    public function save(Job $job)
    {
        abort_if(!auth()->user()->isUser(), 403);
 
        $user = auth()->user();
        if ($user->hasSaved($job)) {
            $user->savedJobs()->detach($job->id);
            $saved = false;
            $message = __('messages.job_unsaved');
        } else {
            $user->savedJobs()->attach($job->id);
            $saved = true;
            $message = __('messages.job_saved');
        }
 
        return response()->json(compact('saved', 'message'));
    }
 
    public function recommended()
    {
        abort_if(!auth()->user()->isUser(), 403);
 
        $jobs = auth()->user()->getRecommendedJobs(20);
        return view('jobs.recommended', compact('jobs'));
    }
}
 