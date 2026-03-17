<?php

namespace App\Providers;

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
        // Register observers
        \App\Models\ActualEventParticipant::observe(\App\Observers\ActualEventParticipantObserver::class);
        \App\Models\ActualEvent::observe(\App\Observers\ActualEventObserver::class);
        \App\Models\AnnualPlan::observe(\App\Observers\AnnualPlanObserver::class);
    }
}
