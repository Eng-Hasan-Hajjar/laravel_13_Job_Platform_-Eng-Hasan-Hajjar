<?php


// ==========================================
// app/Notifications/ApplicationSubmitted.php
// ==========================================
namespace App\Notifications;
 
use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class ApplicationSubmitted extends Notification implements ShouldQueue
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
            'title'      => '✅ Application Submitted',
            'body'       => "Your application for {$this->application->job->title} at {$this->application->job->company->name} has been submitted successfully.",
            'action_url' => route('user.applications'),
            'job_title'  => $this->application->job->title,
            'company'    => $this->application->job->company->name,
        ];
    }
 
    public function toMail($notifiable): MailMessage
    {
        $job = $this->application->job;
        return (new MailMessage())
            ->subject("Application Submitted: {$job->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("✅ Your application for **{$job->title}** at **{$job->company->name}** has been submitted successfully!")
            ->line("You can track your application status in your dashboard.")
            ->action('Track Application', route('user.applications'))
            ->line("Good luck! 🍀");
    }
}
 

