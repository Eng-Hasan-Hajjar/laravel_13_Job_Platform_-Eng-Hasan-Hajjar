<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        
        // If no company exists yet, redirect to create profile
        if (!$company) {
            return redirect()->route('company.profile')
                ->with('warning', 'Please complete your company profile first.');
        }
        
        // Load relationships needed for stats
        $company->load(['jobs.applications', 'reviews']);
        
        // Calculate statistics
        $stats = $this->getStats($company);
        
        // Get active jobs
        $activeJobs = $company->jobs()
            ->active()
            ->withCount('applications')
            ->latest()
            ->take(5)
            ->get();
        
        // Get recent applications
        $recentApplications = JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->with(['user', 'job'])
            ->latest()
            ->take(10)
            ->get();
        
        // Prepare chart data (last 30 days)
        $chartData = $this->getChartData($company);
        
        return view('company.dashboard', compact(
            'company', 
            'stats', 
            'activeJobs', 
            'recentApplications', 
            'chartData'
        ));
    }
    
    private function getStats($company)
    {
        $activeJobsCount = $company->jobs()->active()->count();
        $totalApplications = $company->jobs()->withCount('applications')->get()->sum('applications_count');
        
        // Applications this month
        $jobsThisMonth = $company->jobs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // New applications (last 30 days)
        $newApplications = JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        // Pending applications
        $pendingApplications = JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->where('status', 'pending')
            ->count();
        
        return [
            'active_jobs' => $activeJobsCount,
            'total_applications' => $totalApplications,
            'jobs_this_month' => $jobsThisMonth,
            'new_applications' => $newApplications,
            'pending_applications' => $pendingApplications,
        ];
    }
    
    private function getChartData($company)
    {
        // Get applications grouped by date for the last 30 days
        $applications = JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        // Prepare labels and data for the last 30 days
        $labels = [];
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[] = $applications->has($date) ? $applications[$date]->total : 0;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
    
    public function applicationsIndex(Request $request)
    {
        $company = Auth::user()->company;
        
        if (!$company) {
            return redirect()->route('company.profile')
                ->with('warning', 'Please complete your company profile first.');
        }
        
        $status = $request->get('status');
        
        $applications = JobApplication::whereHas('job', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->with(['user', 'job'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);
        
        return view('company.applications', compact('applications', 'status'));
    }
    
    public function showApplication(JobApplication $application)
    {
        $company = Auth::user()->company;
        
        // Ensure the application belongs to this company
        if ($application->job->company_id !== $company->id) {
            abort(403, 'Unauthorized access to this application.');
        }
        
        $application->load(['user', 'job']);
        
        return view('company.application-detail', compact('application'));
    }
    
    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        $company = Auth::user()->company;
        
        // Ensure the application belongs to this company
        if ($application->job->company_id !== $company->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,reviewed,shortlisted,accepted,rejected',
        ]);
        
        $application->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Application status updated successfully'
        ]);
    }
    
    public function toggleJobStatus(Request $request, \App\Models\Job $job)
    {
        $company = Auth::user()->company;
        
        // Ensure the job belongs to this company
        if ($job->company_id !== $company->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $job->update(['is_active' => !$job->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $job->is_active,
            'message' => $job->is_active ? 'Job activated successfully' : 'Job deactivated successfully'
        ]);
    }
}