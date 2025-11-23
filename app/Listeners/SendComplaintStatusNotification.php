<?php

namespace App\Listeners;

use App\Events\ComplaintStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ComplaintStatusNotification; 

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
        $user = $event->complaint->user;

        if ($user && $user->email) {
            $user->notify(new ComplaintStatusNotification($event->complaint));
        } else {
           
            \Illuminate\Support\Facades\Log::error('Notification failed: User or email not found for complaint ID: ' . $event->complaint->id);
        }
    }
}
