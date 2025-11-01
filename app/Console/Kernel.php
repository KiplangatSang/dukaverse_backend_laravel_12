<?php

namespace App\Console;

use App\Jobs\CheckEmailsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the populate:leads command to run daily at 9 AM
        $schedule->command('populate:leads')->dailyAt('09:00');

        // Schedule email checking every 5 minutes
        $schedule->job(CheckEmailsJob::class)->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
