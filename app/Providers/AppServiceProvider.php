<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;

use App\Contracts\ComplaintRepositoryInterface;
use App\Repositories\EloquentComplaintRepository;

use App\Models\Complaint; // 💡 تأكد من استيراد نموذج الشكوى
use App\Events\ComplaintStatusUpdated;

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

        $this->app->bind(
            ComplaintRepositoryInterface::class,
            EloquentComplaintRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 💡 1. الاستماع لحدث التحديث في نموذج الشكوى
        Complaint::updated(function (Complaint $complaint) {

            // 💡 2. التحقق من أن حقل 'status' قد تغير بالفعل
            if ($complaint->isDirty('status')) {

                // 💡 3. إطلاق الحدث الذي سينبه الـ Listener
                event(new ComplaintStatusUpdated($complaint));
            }
        });
    }
}
