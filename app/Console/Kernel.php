<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        // Load custom artisan commands
        $this->load(__DIR__.'/Commands');

        // Include any console route-based commands (optional)
        require base_path('routes/console.php');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Example: run GPS listener every minute
        // Consider running via Supervisor instead if it must persist
        $schedule->command('gps:listen')->everyMinute();

        // Alternatively, run every 5 minutes:
        // $schedule->command('gps:listen')->everyFiveMinutes();

        // For now, it's often better to run `php artisan gps:listen` manually
    }

    /**
     * Register custom Artisan commands.
     */
protected $commands = [
        \App\Console\Commands\ListenToGPSUpdates::class,
    ];
}
