<?php

namespace App\Providers;

use App\Mail\FailedJobNotification;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::failing(function (JobFailed $event) {
            // Notify admin of the failure
            $adminEmail = config('mail.admin_email');
            try {
                Mail::to($adminEmail)->queue(new FailedJobNotification($event));
            } catch (\Exception $e) {
                logger()->error("Failed to send failed job notification: {$e->getMessage()}");
            }
        });
    }
}
