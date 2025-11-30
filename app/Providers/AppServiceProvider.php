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
       
        Complaint::updated(function (Complaint $complaint) {

           
            if ($complaint->isDirty('status')) {

                event(new ComplaintStatusUpdated($complaint));
            }
        });
    }
}
