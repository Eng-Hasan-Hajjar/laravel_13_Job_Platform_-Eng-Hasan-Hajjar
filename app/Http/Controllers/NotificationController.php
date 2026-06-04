<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of user notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query based on filter
        $query = $user->notifications();
        
        // Apply filter
        $filter = $request->get('filter', 'all');
        
        switch ($filter) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'job':
                $query->where('data->type', 'job');
                break;
            case 'application':
                $query->where('data->type', 'application');
                break;
            case 'system':
                $query->where('data->type', 'system');
                break;
            case 'all':
            default:
                // No filter
                break;
        }
        
        // Get notifications with pagination
        $notifications = $query->paginate(15);
        
        // Get unread count for header
        $unreadCount = $user->unreadNotifications()->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }
    
    /**
     * Get latest notifications for AJAX polling.
     */
    public function latest(Request $request)
    {
        $user = Auth::user();
        
        // Get unread count
        $unreadCount = $user->unreadNotifications()->count();
        
        // Get latest 5 notifications
        $latestNotifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'body' => $data['body'] ?? '',
                    'type' => $data['type'] ?? 'system',
                    'is_read' => !is_null($notification->read_at),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'action_url' => $data['action_url'] ?? '#',
                    'icon' => $this->getNotificationIcon($data['type'] ?? 'system')
                ];
            });
        
        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $latestNotifications
        ]);
    }
    
    /**
     * Mark a notification as read.
     */
    public function markRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', __('messages.notification_marked_read'));
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', __('messages.all_notifications_marked_read'));
    }
    
    /**
     * Mark notifications as seen (for badge update).
     */
    public function markSeen(Request $request)
    {
        $user = Auth::user();
        
        // Optional: update seen_at timestamp
        if (method_exists($user, 'updateLastSeenAt')) {
            $user->updateLastSeenAt();
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get unread notifications count for badge.
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
    
    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        
        return back()->with('success', __('messages.notification_deleted'));
    }
    
    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request)
    {
        $user = Auth::user();
        $user->notifications()->delete();
        
        return back()->with('success', __('messages.all_notifications_deleted'));
    }
    
    /**
     * Get notification icon based on type.
     */
    private function getNotificationIcon($type)
    {
        return match ($type) {
            'job' => 'fa-briefcase',
            'application' => 'fa-file-alt',
            'message' => 'fa-envelope',
            'alert' => 'fa-exclamation-circle',
            'success' => 'fa-check-circle',
            'system' => 'fa-bell',
            default => 'fa-bell'
        };
    }
}