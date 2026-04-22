<?php



// ==========================================
// app/Notifications/NewJobPosted.php (for subscribed users)
// ==========================================
namespace App\Notifications;
 
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class NewJobPosted extends Notification implements ShouldQueue
{
    use Queueable;
 
    public function __construct(public Job $job) {}
 
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }
 
    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'job',
            'title'      => "🆕 New Job: {$this->job->title}",
            'body'       => "{$this->job->company->name} posted a new job matching your preferences.",
            'action_url' => route('jobs.show', $this->job->slug),
            'job_title'  => $this->job->title,
            'company'    => $this->job->company->name,
            'location'   => $this->job->location,
            'type_label' => $this->job->type,
        ];
    }
 
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("New Job Match: {$this->job->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new job matching your profile has been posted:")
            ->line("**{$this->job->title}** at **{$this->job->company->name}**")
            ->line("📍 {$this->job->location} | 💼 {$this->job->type}")
            ->action('View Job', route('jobs.show', $this->job->slug))
            ->line("Don't miss this opportunity!");
    }
}