<?php


// ==========================================
// app/Notifications/ApplicationStatusChanged.php
// ==========================================
namespace App\Notifications;
 
use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;
 
    private array $statusMessages = [
        'reviewed'    => ['icon' => 'fa-eye',         'color' => 'blue',   'title' => 'Application Reviewed'],
        'shortlisted' => ['icon' => 'fa-star',        'color' => 'yellow', 'title' => 'You\'re Shortlisted! 🌟'],
        'accepted'    => ['icon' => 'fa-check-circle','color' => 'green',  'title' => 'Application Accepted! 🎉'],
        'rejected'    => ['icon' => 'fa-times-circle','color' => 'red',    'title' => 'Application Update'],
    ];
 
    public function __construct(public JobApplication $application) {}
 
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }
 
    public function toDatabase($notifiable): array
    {
        $statusInfo = $this->statusMessages[$this->application->status] ?? [
            'type'  => 'application',
            'title' => 'Application Update',
        ];
 
        return [
            'type'       => 'application',
            'title'      => $statusInfo['title'],
            'body'       => "Your application for {$this->application->job->title} at {$this->application->job->company->name} has been {$this->application->status}.",
            'action_url' => route('user.applications'),
            'status'     => $this->application->status,
            'job_title'  => $this->application->job->title,
            'company'    => $this->application->job->company->name,
        ];
    }
 
    public function toMail($notifiable): MailMessage
    {
        $statusInfo = $this->statusMessages[$this->application->status] ?? ['title' => 'Application Update'];
        $job = $this->application->job;
 
        $mail = (new MailMessage())
            ->subject("Application Update: {$job->title}")
            ->greeting("Hello {$notifiable->name}!");
 
        match($this->application->status) {
            'accepted'    => $mail->line("🎉 Congratulations! Your application for **{$job->title}** has been **accepted**!")
                                  ->line("The company will contact you soon."),
            'shortlisted' => $mail->line("⭐ Great news! You've been **shortlisted** for **{$job->title}**!")
                                  ->line("The company will be in touch for next steps."),
            'rejected'    => $mail->line("Thank you for applying to **{$job->title}**.")
                                  ->line("After careful consideration, the company has decided to move forward with other candidates."),
            default       => $mail->line("Your application for **{$job->title}** status has been updated to: {$this->application->status}."),
        };
 
        if ($this->application->admin_notes) {
            $mail->line("**Note from company:** " . $this->application->admin_notes);
        }
 
        return $mail->action('View My Applications', route('user.applications'))
                    ->line("Best of luck in your job search!");
    }
}
