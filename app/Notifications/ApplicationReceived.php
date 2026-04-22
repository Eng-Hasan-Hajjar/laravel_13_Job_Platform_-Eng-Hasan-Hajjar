<?php
// ==========================================
// app/Notifications/ApplicationReceived.php
// ==========================================
namespace App\Notifications;
 
use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class ApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;
 
    public function __construct(public JobApplication $application) {}
 
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }
 
    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'application',
            'title'      => 'طلب توظيف جديد / New Application',
            'body'       => sprintf(
                '%s applied for %s',
                $this->application->user->name,
                $this->application->job->title
            ),
            'action_url' => route('company.applications.show', $this->application->id),
            'applicant'  => $this->application->user->name,
            'job_title'  => $this->application->job->title,
        ];
    }
 
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("New Application: {$this->application->job->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$this->application->user->name} has applied for your job: **{$this->application->job->title}**")
            ->action('Review Application', route('company.applications.show', $this->application->id))
            ->line('Please review the application at your earliest convenience.');
    }
}