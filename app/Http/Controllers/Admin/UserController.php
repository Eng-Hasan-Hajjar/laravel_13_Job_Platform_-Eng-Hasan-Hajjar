<?php
// ==========================================
// app/Http/Controllers/Admin/UserController.php
// ==========================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->when($request->q, fn($q, $s) => $q->where(fn($sub) => $sub
                ->where('name',  'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
            ))
            ->when($request->role,   fn($q, $r) => $q->where('role', $r))
            ->when($request->status, function ($q, $s) {
                if ($s === 'active')   $q->where('is_active', true);
                if ($s === 'inactive') $q->where('is_active', false);
            });

        match ($request->sort ?? 'latest') {
            'oldest'   => $query->oldest(),
            'name_asc' => $query->orderBy('name'),
            default    => $query->latest(),
        };

        $users = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => User::count(),
            'users'     => User::where('role', 'user')->count(),
            'companies' => User::where('role', 'company')->count(),
            'admins'    => User::where('role', 'admin')->count(),
            'active'    => User::where('is_active', true)->count(),
            'inactive'  => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user)
    {
        $user->load(['company', 'jobApplications.job.company']);

        $stats = [
            'applications' => $user->jobApplications()->count(),
            'accepted'     => $user->jobApplications()->where('status', 'accepted')->count(),
            'pending'      => $user->jobApplications()->where('status', 'pending')->count(),
            'saved_jobs'   => $user->savedJobs()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('messages.cannot_disable_self'));
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', $user->is_active
            ? __('messages.user_activated')
            : __('messages.user_deactivated'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('messages.cannot_delete_self'));
        }

        $user->delete();
        return back()->with('success', __('messages.user_deleted'));
    }
}