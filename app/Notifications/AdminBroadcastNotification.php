<?php



// ==========================================
// app/Notifications/AdminBroadcastNotification.php
// ==========================================
namespace App\Notifications;
 
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class AdminBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;
 
    public function __construct(public array $data) {}
 
    public function via($notifiable): array { return ['database']; }
 
    public function toDatabase($notifiable): array
    {
        return array_merge($this->data, ['type' => 'system']);
    }
}

