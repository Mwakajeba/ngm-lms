<?php

namespace App\Providers;

use App\Jobs\CollectMatureInterestJob;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Schedule mature interest collection to run daily at 6:00 AM
            $schedule->job(new CollectMatureInterestJob())
                // ->dailyAt('00:00')
                ->everyMinute()
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/mature-interest-collection.log'));
        });
    }
}