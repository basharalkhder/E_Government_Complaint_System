<?php

namespace App\Listeners;

use App\Events\ComplaintStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ComplaintStatusNotification;
use Illuminate\Support\Facades\Log;
use App\Notifications\RequestInfoNotification;
use App\Enums\ComplaintStatus;

use App\Models\Complaint;

class SendComplaintStatusNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ComplaintStatusUpdated $event): void
    {
        $complaint = $event->complaint;
        $user = $complaint->user;

        // 1. التحقق الأساسي
        if (!$user || !$user->email) {
            Log::error('Notification failed: User or email not found for complaint ID: ' . $complaint->id);
            return;
        }

        // 2. اختيار الإشعار المناسب بناءً على حالة الشكوى
        $notification = null;

        switch ($complaint->status) {
            case ComplaintStatus::REQUESTED_INFO:
                // عند طلب معلومات، نستخدم الإشعار الخاص الذي يعرض ملاحظات الإدارة
                $notification = new RequestInfoNotification($complaint);
                break;

            case ComplaintStatus::RESOLVED: 
            case ComplaintStatus::REJECTED:
            case ComplaintStatus::IN_PROGRESS:
                $notification = new ComplaintStatusNotification($complaint);
                break;

            default:
                // لا نفعل شيئاً للحالات التي لا تتطلب إشعاراً فورياً
                return;
        }

        // 3. إرسال الإشعار
        if ($notification) {
            $user->notify($notification);
        }
    }
}
