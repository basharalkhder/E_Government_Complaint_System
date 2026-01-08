<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;

use App\Models\Complaint;
use App\Events\ComplaintStatusUpdated;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Models\User;
use Illuminate\Auth\Events\Failed;

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

        // 2. تتبع وإشعار محاولات الدخول الفاشلة (المتطلب الجديد)
        Event::listen(function (Failed $event) {
            // تسجيل المحاولة الفاشلة في السجلات (Tracing)
            activity()
                ->useLog('Authentication-Failed')
                ->withProperties([
                    'email' => $event->credentials['email'] ?? 'unknown',
                    'ip' => request()->ip()
                ])
                ->log('Failed login attempt detected');

            // إرسال الإشعار للمستخدم إذا كان الحساب موجوداً فعلياً
            if ($event->user instanceof User) {
                $event->user->notify(new \App\Notifications\LoginFailedNotification());
            }
        });


        Complaint::updated(function (Complaint $complaint) {


            if ($complaint->isDirty('status')) {

                event(new ComplaintStatusUpdated($complaint));
            }
        });
    }
}
