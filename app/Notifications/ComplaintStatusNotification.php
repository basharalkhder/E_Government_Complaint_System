<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintStatusNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("تحديث حالة الشكوى: {$this->complaint->reference_number}")
                    ->greeting("مرحباً بك،")
                    ->line("تم تحديث حالة الشكوى رقم **{$this->complaint->reference_number}** إلى **{$this->complaint->status->value}**.")
                    ->line('يمكنك متابعة التفاصيل داخل التطبيق.')
                    ->action('عرض الشكوى', url('/complaints/' . $this->complaint->id)) 
                    ->salutation('مع تحيات فريق الدعم.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
