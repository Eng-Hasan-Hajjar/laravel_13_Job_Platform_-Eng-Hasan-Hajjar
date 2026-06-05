<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->jobApplications()
            ->with(['job.company', 'job.category'])
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->when($request->sort === 'oldest', fn($q) => $q->oldest(), fn($q) => $q->latest());

        $applications = $query->paginate(15)->withQueryString();

        $stats = [
            'total'       => $user->jobApplications()->count(),
            'pending'     => $user->jobApplications()->where('status', 'pending')->count(),
            'shortlisted' => $user->jobApplications()->where('status', 'shortlisted')->count(),
            'accepted'    => $user->jobApplications()->where('status', 'accepted')->count(),
        ];

        return view('user.applications', compact('applications', 'stats'));
    }

    public function downloadCv($id)
    {
        $application = auth()->user()->jobApplications()->findOrFail($id);

        if (!$application->cv_path) {
            return back()->with('error', __('messages.no_cv'));
        }

        return Storage::disk('local')->download(
            $application->cv_path,
            'CV-' . auth()->user()->name . '.pdf'
        );
    }
}