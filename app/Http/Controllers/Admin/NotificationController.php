<?php



// ==========================================
// app/Http/Controllers/Admin/NotificationController.php
// ==========================================
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Http\Request;
 
class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index');
    }
 
    public function send(Request $request)
    {
        $request->validate([
            'target'     => 'required|in:all,users,companies',
            'title'      => 'required|max:100',
            'body'       => 'required|max:500',
            'action_url' => 'nullable|url',
        ]);
 
        $query = User::query();
        if ($request->target === 'users')     $query->where('role', 'user');
        if ($request->target === 'companies') $query->where('role', 'company');
 
        $users = $query->get();
        $data  = $request->only(['title', 'body', 'action_url']);
        $data['type'] = 'system';
 
        foreach ($users as $user) {
            $user->notify(new AdminBroadcastNotification($data));
        }
 
        return back()->with('success', "Notification sent to {$users->count()} users.");
    }
}

