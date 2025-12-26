<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;

use App\Models\Complaint;
use App\Events\ComplaintStatusUpdated;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // 1. Tracing: تتبع تسجيل دخول المستخدمين للنظام
        Event::listen(function (Login $event) {
            /** @var \App\Models\User $user */
            $user = $event->user;
            activity()
                ->causedBy($user)
                ->useLog('Authentication')
                ->log('User logged into the system');
        });


        Complaint::updated(function (Complaint $complaint) {


            if ($complaint->isDirty('status')) {

                event(new ComplaintStatusUpdated($complaint));
            }
        });
    }
}
