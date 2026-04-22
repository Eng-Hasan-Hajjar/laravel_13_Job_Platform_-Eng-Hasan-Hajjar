<?php

// ==========================================
// app/Http/Controllers/HomeController.php
// ==========================================
namespace App\Http\Controllers;
 
use App\Models\{Job, Company, Category, User};
use Illuminate\Http\Request;
 
class HomeController extends Controller
{
    public function index()
    {
        $featuredJobs = Job::with(['company', 'category'])
            ->active()
            ->featured()
            ->latest()
            ->take(6)
            ->get();
 
        $categories = Category::active()
            ->withCount(['jobs' => fn($q) => $q->active()])
            ->orderByDesc('jobs_count')
            ->take(8)
            ->get();
 
        $topCompanies = Company::active()
            ->verified()
            ->withCount(['reviews', 'jobs' => fn($q) => $q->active()])
            ->orderByDesc('jobs_count')
            ->take(4)
            ->get();
 
        $locations = Job::active()
            ->distinct()
            ->pluck('location')
            ->filter()
            ->take(20);
 
        $stats = [
            'total_jobs'      => Job::active()->count(),
            'total_companies' => Company::active()->count(),
            'total_users'     => User::where('role', 'user')->count(),
            'placements'      => \App\Models\JobApplication::where('status', 'accepted')->count(),
        ];
 
        return view('home', compact('featuredJobs', 'categories', 'topCompanies', 'locations', 'stats'));
    }
}

