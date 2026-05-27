<?php
// app/Http/Controllers/Company/ApplicationController.php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\{Job, JobApplication};
use App\Services\AI\CandidateRankingService;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function __construct(private CandidateRankingService $ai) {}

    private function company() { return auth()->user()->company; }

    // ════════════════════════════════════════════════════════════════
    //  قائمة الطلبات — عادية أو AI
    // ════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $company = $this->company();
        $useAI   = $request->boolean('ai', false);
        $jobId   = $request->integer('job_id');
        $jobs    = $company->jobs()->get(['id', 'title']);

        if ($jobId && $useAI) {
            // ── وضع الذكاء الاصطناعي ────────────────────────────────
            $selectedJob  = Job::findOrFail($jobId);
            abort_if($selectedJob->company_id !== $company->id, 403);

            $applications = $this->ai->rank($selectedJob, true);
            $aiModeActive = true;
        } else {
            // ── الوضع العادي ─────────────────────────────────────────
            $applications = JobApplication::with(['user', 'job'])
                ->whereHas('job', fn($q) => $q->where('company_id', $company->id))
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($jobId, fn($q) => $q->where('job_id', $jobId))
                ->latest()
                ->paginate(20);

            $aiModeActive = false;
            $selectedJob  = $jobId ? Job::find($jobId) : null;
        }

        return view('company.applications.index', compact(
            'applications', 'jobs', 'useAI', 'aiModeActive', 'selectedJob'
        ));
    }

    // ════════════════════════════════════════════════════════════════
    //  تفاصيل متقدم واحد مع تحليل AI
    // ════════════════════════════════════════════════════════════════
    public function show(JobApplication $application)
    {
        abort_if($application->job->company_id !== $this->company()->id, 403);
        $application->load(['user', 'job']);

        // ترتيب كامل للوظيفة للحصول على بيانات هذا المتقدم
        $ranked      = $this->ai->rank($application->job, true);
        $aiApplication = $ranked->firstWhere('id', $application->id);

        $rank            = $ranked->search(fn($a) => $a->id === $application->id) + 1;
        $totalApplicants = $application->job->applications()->count();
        $cvAnalysis      = $application->user->cv_analyzed;

        return view('company.applications.show', compact(
            'application', 'cvAnalysis', 'aiApplication', 'rank', 'totalApplicants'
        ));
    }

    // ════════════════════════════════════════════════════════════════
    //  API: ترتيب AI لوظيفة (AJAX)
    // ════════════════════════════════════════════════════════════════
    public function rankApi(Request $request, Job $job)
    {
        abort_if($job->company_id !== $this->company()->id, 403);

        $useAI  = $request->boolean('ai', true);
        $ranked = $this->ai->rank($job, $useAI);

        return response()->json([
            'ai_on'      => $useAI,
            'total'      => $ranked->count(),
            'candidates' => $ranked->map(fn($app) => [
                'id'          => $app->id,
                'name'        => $app->user->name,
                'email'       => $app->user->email,
                'ai_score'    => $app->ai_score    ?? 0,
                'skill_match' => $app->skill_match_pct ?? 0,
                'ai_summary'  => $app->ai_summary  ?? '',
                'status'      => $app->status,
                'applied_at'  => $app->created_at->diffForHumans(),
                'url'         => route('company.applications.show', $app->id),
            ]),
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    //  تحديث حالة الطلب
    // ════════════════════════════════════════════════════════════════
    public function updateStatus(Request $request, JobApplication $application)
    {
        abort_if($application->job->company_id !== $this->company()->id, 403);

        $request->validate([
            'status'      => 'required|in:pending,reviewed,shortlisted,accepted,rejected',
            'admin_notes' => 'nullable|max:1000',
        ]);

        $application->updateStatus($request->status, $request->admin_notes);
        $application->user->notify(new ApplicationStatusChanged($application));

        return response()->json(['success' => true, 'message' => __('messages.status_updated'), 'status' => $request->status]);
    }

    public function downloadCv(JobApplication $application)
    {
        abort_if($application->job->company_id !== $this->company()->id, 403);
        return Storage::disk('private')->download($application->cv_path, 'CV-'.$application->user->name.'.pdf');
    }
}
