<?php
// ==========================================
// app/Http/Controllers/Auth/LoginController.php
// ==========================================
namespace App\Http\Controllers\Auth;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
 
class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }
 
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);
 
        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');
 
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
 
        $user = Auth::user();
 
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('messages.account_deactivated'),
            ]);
        }
 
        // Update last seen
        $user->update(['last_seen_at' => now()]);
 
        // Set locale from user preference
        if ($user->locale) {
            session(['locale' => $user->locale]);
        }
 
        $request->session()->regenerate();
 
        // Redirect based on role
        return redirect()->intended($this->redirectTo($user));
    }
 
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', __('messages.logged_out'));
    }
 
    private function redirectTo($user): string
    {
        return match($user->role) {
            'admin'   => route('admin.dashboard'),
            'company' => route('company.dashboard'),
            default   => route('home'),
        };
    }
}