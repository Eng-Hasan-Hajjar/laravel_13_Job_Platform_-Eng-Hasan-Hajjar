<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
        // أضف 'mail' هنا إذا أردت إرسال إيميل ترحيب أيضاً
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'success',
            'title'      => '🎉 مرحباً بك في المنصة!',
            'body'       => 'أهلاً ' . $this->user->name . '، يسعدنا انضمامك. أكمل ملفك الشخصي للحصول على توصيات مخصصة.',
            'action_url' => $this->user->role === 'company'
                ? route('company.profile')
                : route('user.profile'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isCompany = $this->user->role === 'company';

        return (new MailMessage())
            ->subject('مرحباً بك في ' . config('app.name') . ' 🎉')
            ->greeting('مرحباً ' . $this->user->name . '!')
            ->line('يسعدنا انضمامك إلى منصتنا.')
            ->line($isCompany
                ? 'ابدأ بإكمال ملف شركتك ونشر أول وظيفة.'
                : 'ابدأ بإكمال ملفك الشخصي ورفع سيرتك الذاتية.')
            ->action(
                $isCompany ? 'إكمال ملف الشركة' : 'إكمال ملفي الشخصي',
                $isCompany ? route('company.profile') : route('user.profile')
            );
    }
}