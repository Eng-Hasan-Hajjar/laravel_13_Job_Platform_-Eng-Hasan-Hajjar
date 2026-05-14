<?php
// ==========================================
// app/Http/Controllers/Auth/RegisterController.php
// ==========================================
namespace App\Http\Controllers\Auth;
 
use App\Http\Controllers\Controller;
use App\Models\{User, Company};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Support\Str;
 
class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }
 
    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|min:3|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'role'         => 'required|in:user,company',
            'company_name' => 'required_if:role,company|string|max:255',
            'terms'        => 'required|accepted',
        ]);
 
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
            'locale'    => app()->getLocale(),
        ]);
 
        // If company role, create company record
        if ($request->role === 'company') {
            Company::create([
                'user_id'  => $user->id,
                'name'     => $request->company_name,
                'slug'     => Str::slug($request->company_name) . '-' . Str::random(5),
            ]);
        }
 
        Auth::login($user);
 
        // Send welcome notification
        $user->notify(new \App\Notifications\WelcomeNotification($user));
 
        $redirect = $request->role === 'company'
            ? route('company.profile')
            : route('user.profile');
 
        return redirect($redirect)->with('success', __('messages.welcome_registered'));
    }
}
 