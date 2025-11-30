<?php

namespace App\Notifications;


use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestInfoNotification extends Notification
{
    use Queueable;

    protected Complaint $complaint;

    /**
     * Create a new notification instance.
     */
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
            ->subject("طلب معلومات إضافية بخصوص الشكوى رقم {$this->complaint->reference_number}")
            ->greeting("عزيزي/عزيزتي {$notifiable->name},")
            ->line('نود إعلامك بأن الجهة المسؤولة عن معالجة شكواك بحاجة إلى معلومات إضافية لإتمام العمل.')
            
            // عرض الملاحظات الإدارية التي كتبها الموظف
            ->line('**المطلوب منك (ملاحظات الجهة):**')
            ->line($this->complaint->admin_notes)
            
            ->action('الرد على الشكوى', url('/complaints/' . $this->complaint->id)) // يمكنك تعديل المسار حسب تطبيقك
            ->line('يرجى الرد على طلب المعلومات في أقرب وقت ممكن. شكراً لتعاونك.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'reference_number' => $this->complaint->reference_number,
            'message' => 'تم طلب معلومات إضافية من الجهة المسؤولة.',
        ];
    }
}
