<?php



// ==========================================
// app/Http/Controllers/Company/ApplicationController.php
// ==========================================
namespace App\Http\Controllers\Company;
 
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Notifications\ApplicationStatusChanged;
use App\Services\CvAnalysisService;
use Illuminate\Http\Request;
 
class ApplicationController extends Controller
{
    private function company() { return auth()->user()->company; }
 
    public function index(Request $request)
    {
        $applications = JobApplication::with(['user', 'job'])
            ->whereHas('job', fn($q) => $q->where('company_id', $this->company()->id))
            ->status($request->status)
            ->when($request->job_id, fn($q) => $q->where('job_id', $request->job_id))
            ->latest()
            ->paginate(20);
 
        $jobs = $this->company()->jobs()->get(['id', 'title']);
 
        return view('company.applications.index', compact('applications', 'jobs'));
    }
 
    public function show(JobApplication $application)
    {
        abort_if($application->job->company_id !== $this->company()->id, 403);
        $application->load(['user', 'job']);
 
        // Get CV analysis if available
        $cvAnalysis = $application->user->cv_analyzed;
 
        return view('company.applications.show', compact('application', 'cvAnalysis'));
    }
 
    public function updateStatus(Request $request, JobApplication $application)
    {
        abort_if($application->job->company_id !== $this->company()->id, 403);
 
        $request->validate([
            'status'      => 'required|in:pending,reviewed,shortlisted,accepted,rejected',
            'admin_notes' => 'nullable|max:1000',
        ]);
 
        $application->updateStatus($request->status, $request->admin_notes);
 
        // Notify applicant
        $application->user->notify(new ApplicationStatusChanged($application));
 
        return response()->json([
            'success' => true,
            'message' => __('messages.status_updated'),
            'status'  => $request->status,
        ]);
    }
 
    public function downloadCv(JobApplication $application)
    {
        abort_if($application->job->company_id !== $this->company()->id, 403);
 
        return Storage::disk('private')->download(
            $application->cv_path,
            'CV-' . $application->user->name . '.pdf'
        );
    }
}
 


