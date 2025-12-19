<?php

namespace App\Providers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Observers\EnrollmentObserver;
use App\Observers\PaymentObserver;
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
        // Register model observers
        Enrollment::observe(EnrollmentObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}
