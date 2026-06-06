<?php
// ==========================================
// app/Http/Controllers/Admin/CompanyController.php
// ==========================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with('user')->withCount(['jobs', 'reviews'])
            ->when($request->q, fn($q, $s) => $q->where(fn($sub) => $sub
                ->where('name',     'like', "%{$s}%")
                ->orWhere('industry','like', "%{$s}%")
                ->orWhere('location','like', "%{$s}%")
            ))
            ->when($request->status, function ($q, $s) {
                if ($s === 'verified')   $q->where('is_verified', true);
                if ($s === 'unverified') $q->where('is_verified', false);
                if ($s === 'active')     $q->where('is_active', true);
                if ($s === 'inactive')   $q->where('is_active', false);
            });

        match ($request->sort ?? 'latest') {
            'oldest'    => $query->oldest(),
            'name_asc'  => $query->orderBy('name'),
            'most_jobs' => $query->orderByDesc('jobs_count'),
            default     => $query->latest(),
        };

        $companies = $query->paginate(15)->withQueryString();

        $stats = [
            'total'      => Company::count(),
            'verified'   => Company::where('is_verified', true)->count(),
            'unverified' => Company::where('is_verified', false)->count(),
            'active'     => Company::where('is_active', true)->count(),
        ];

        return view('admin.companies.index', compact('companies', 'stats'));
    }

    public function show(Company $company)
    {
        $company->load(['user', 'jobs.applications', 'reviews.user']);

        $stats = [
            'total_jobs'    => $company->jobs->count(),
            'active_jobs'   => $company->jobs->where('is_active', true)->count(),
            'applications'  => $company->jobs->sum(fn($j) => $j->applications->count()),
            'reviews'       => $company->reviews->count(),
            'avg_rating'    => round($company->reviews->avg('rating') ?? 0, 1),
        ];

        return view('admin.companies.show', compact('company', 'stats'));
    }

    public function verify(Company $company)
    {
        $company->update(['is_verified' => !$company->is_verified]);

        return back()->with('success', $company->is_verified
            ? __('messages.company_verified')
            : __('messages.company_unverified'));
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return back()->with('success', __('messages.company_deleted'));
    }
}