<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $preferences = [
            'language' => $user->locale ?? app()->getLocale(),
            'timezone' => $user->timezone ?? config('app.timezone'),
            'email_notifications' => $user->email_notifications ?? true,
            'push_notifications' => $user->push_notifications ?? true,
            'job_alerts' => $user->job_alerts ?? true,
            'newsletter' => $user->newsletter ?? false,
        ];
        
        return view('settings.index', compact('user', 'preferences'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
        ]);
        
        $user->update($validated);
        
        return back()->with('success', __('messages.profile_updated'));
    }
    
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return back()->with('success', __('messages.password_updated'));
    }
    
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'language' => 'required|in:ar,en',
            'timezone' => 'required|timezone',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'job_alerts' => 'boolean',
            'newsletter' => 'boolean',
        ]);
        
        if (isset($validated['language'])) {
            session(['locale' => $validated['language']]);
            app()->setLocale($validated['language']);
        }
        
        $user->update($validated);
        
        return back()->with('success', __('messages.preferences_updated'));
    }
    
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $user = Auth::user();
        
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }
        
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);
        
        return response()->json([
            'success' => true,
            'message' => __('messages.avatar_updated'),
            'avatar_url' => $user->avatar_url
        ]);
    }
    
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'confirmation' => 'required|in:DELETE',
        ]);
        
        $user = Auth::user();
        
        Auth::logout();
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', __('messages.account_deleted'));
    }
    
    public function exportData()
    {
        $user = Auth::user();
        
        $data = [
            'user' => $user->only(['name', 'email', 'phone', 'location', 'bio', 'created_at']),
            'applications' => $user->jobApplications()->with('job')->get()->toArray(),
            'saved_jobs' => $user->savedJobs()->get()->toArray(),
            'notifications' => $user->notifications()->get()->toArray(),
        ];
        
        $filename = 'user_data_' . $user->id . '_' . date('Y-m-d') . '.json';
        
        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}