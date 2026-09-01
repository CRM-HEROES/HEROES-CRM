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

        // Automatic assignment of unassigned prospects
        $schedule->command('app:assign-prospects')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('app:reassign-unavailable-prospects')->everyFiveMinutes()->withoutOverlapping();

        // Google Sheets auto-sync: re-download and re-import sheets that
        // opted into periodic sync. Runs often; each import's own
        // sync_interval_minutes decides whether it is actually due.
        $schedule->command('app:sync-google-sheets-imports')->everyFiveMinutes()->withoutOverlapping();

        // ARCHER (P6): nightly prospect enrichment. Rank runs an hour after
        // enrich to give the queue time to work through the batch.
        $schedule->command('archer:enrich')->dailyAt('02:00')->withoutOverlapping();
        $schedule->command('archer:rank')->dailyAt('03:00')->withoutOverlapping();

        // Doctolib appointments sync: not enabled yet, App\Jobs\Doctolib\AppointmentsGet
        // is a stub until Doctolib API/ICS access is configured.
        // $schedule->command('app:doctolib-sync-appointments')->everyFifteenMinutes()->withoutOverlapping();
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
