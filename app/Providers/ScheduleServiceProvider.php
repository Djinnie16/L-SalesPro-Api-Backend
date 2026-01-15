<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\CheckLowStock;

class ScheduleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckLowStock::class,
            ]);
            
            $this->app->booted(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('leys:check-low-stock')->dailyAt('09:00');
            });
        }
    }
}