<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily stat
        $schedule->command('app:stat')->dailyAt('0:10');

        // User Tracking
        $schedule->command('app:user-tracker')->hourly();
        
        // Vehicle Tracking
        // $schedule->command('app:vehicle-tracker')->hourly();
        
        // Label prospects count
        $schedule->command('app:label-prospects-count')->hourly();
        
        // User prospects count
        $schedule->command('app:user-prospects-count')->hourly();
        
        // Role users count
        $schedule->command('app:role-users-count')->hourly();
        
        // Relevance
        $schedule->command('app:relevance --days=120')->daily();

        // Campaign
        // $schedule->command('app:campaign --frequency=once')->everyMinute();
        $schedule->command('app:campaign --frequency=every_fifteen_minutes')->everyFifteenMinutes();
        $schedule->command('app:campaign --frequency=every_thirty_minutes')->everyThirtyMinutes();
        $schedule->command('app:campaign --frequency=hourly')->hourly();
        $schedule->command('app:campaign --frequency=daily')->daily();
        $schedule->command('app:campaign --frequency=monthly')->monthlyOn(1);
        
        $schedule->command('app:campaign --frequency=once')->daily();
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
