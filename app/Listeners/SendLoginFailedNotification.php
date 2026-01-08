<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Notifications\LoginFailedNotification;

class SendLoginFailedNotification
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
    public function handle(object $event): void
    {
        // إذا كان هناك مستخدم مرتبط بهذا الإيميل الذي فشل دخوله
        if ($event->user) {
            // إرسال الإشعار فوراً أو عبر Queue لتحسين الأداء
            $event->user->notify(new LoginFailedNotification());
        }
    }
}
