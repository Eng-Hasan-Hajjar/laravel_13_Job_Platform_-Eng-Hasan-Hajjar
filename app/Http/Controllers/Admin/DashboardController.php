<?php



// ==========================================
// app/Http/Controllers/Admin/DashboardController.php
// ==========================================
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\{User, Company, Job, JobApplication};
use Carbon\Carbon;
 
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'            => User::count(),
            'companies'        => Company::count(),
            'active_jobs'      => Job::active()->count(),
            'applications'     => JobApplication::count(),
            'new_users_today'  => User::whereDate('created_at', today())->count(),
            'verified_companies'=> Company::verified()->count(),
            'jobs_today'       => Job::whereDate('created_at', today())->count(),
            'apps_today'       => JobApplication::whereDate('created_at', today())->count(),
        ];
 
        // 30-day registration chart
        $days = collect(range(29, 0))->map(fn($i) => now()->subDays($i)->format('M d'));
        $regData = collect(range(29, 0))->map(fn($i) => User::whereDate('created_at', now()->subDays($i))->count());
        $regChart = ['labels' => $days->values(), 'data' => $regData->values()];
 
        // Jobs by type chart
        $types = Job::select('type', \DB::raw('count(*) as count'))->groupBy('type')->get();
        $jobTypesChart = [
            'labels' => $types->pluck('type')->map(fn($t) => ucfirst(str_replace('-', ' ', $t))),
            'data'   => $types->pluck('count'),
        ];
 
        $latestUsers     = User::latest()->take(10)->get();
        $latestCompanies = Company::with('user')->withCount('jobs')->latest()->take(10)->get();
        $latestJobs      = Job::with('company')->withCount('applications')->latest()->take(10)->get();
 
        return view('admin.dashboard', compact(
            'stats', 'regChart', 'jobTypesChart',
            'latestUsers', 'latestCompanies', 'latestJobs'
        ));
    }
 
    public function settings()
    {
        return view('admin.settings');
    }
}
 

