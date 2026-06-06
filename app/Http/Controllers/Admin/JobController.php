<?php
// ==========================================
// app/Http/Controllers/Admin/JobController.php
// ==========================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::with(['company', 'category'])->withCount('applications')
            ->when($request->q, fn($q, $s) => $q->where(fn($sub) => $sub
                ->where('title',    'like', "%{$s}%")
                ->orWhere('location','like', "%{$s}%")
                ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%{$s}%"))
            ))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->status, function ($q, $s) {
                if ($s === 'active')   $q->where('is_active', true);
                if ($s === 'inactive') $q->where('is_active', false);
                if ($s === 'featured') $q->where('is_featured', true);
                if ($s === 'expired')  $q->where('deadline', '<', now());
            });

        match ($request->sort ?? 'latest') {
            'oldest'      => $query->oldest(),
            'most_apps'   => $query->orderByDesc('applications_count'),
            'most_views'  => $query->orderByDesc('views_count'),
            default       => $query->latest(),
        };

        $jobs = $query->paginate(20)->withQueryString();

        $stats = [
            'total'        => Job::count(),
            'active'       => Job::where('is_active', true)->count(),
            'featured'     => Job::where('is_featured', true)->count(),
            'expired'      => Job::where('deadline', '<', now())->count(),
            'applications' => \App\Models\JobApplication::count(),
        ];

        return view('admin.jobs.index', compact('jobs', 'stats'));
    }

    public function toggleFeatured(Job $job)
    {
        $job->update(['is_featured' => !$job->is_featured]);

        return back()->with('success', $job->is_featured
            ? __('messages.job_featured')
            : __('messages.job_unfeatured'));
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return back()->with('success', __('messages.job_deleted'));
    }
}