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

        // The aggregation commands below are heavy: they are staggered (not all on the hour),
        // never overlap with themselves (the lock expires after N minutes; it needs a shared
        // cache driver such as redis/file, not "array") and run in the background so a slow
        // one does not delay the other scheduled tasks.

        // User Tracking
        $schedule->command('app:user-tracker')->hourlyAt(3)->withoutOverlapping(30)->runInBackground();
        
        // Vehicle Tracking
        // $schedule->command('app:vehicle-tracker')->hourly();
        
        // Label prospects count
        $schedule->command('app:label-prospects-count')->hourlyAt(8)->withoutOverlapping(15)->runInBackground();
        
        // User prospects count
        $schedule->command('app:user-prospects-count')->hourlyAt(13)->withoutOverlapping(15)->runInBackground();
        
        // Role users count
        $schedule->command('app:role-users-count')->hourlyAt(18)->withoutOverlapping(15)->runInBackground();
        
        // Relevance
        $schedule->command('app:relevance --days=120')->daily();

        // Campaign
        // Each frequency only selects its own campaigns (see Campaign::campaignIds()).
        // "once" runs every minute so that a campaign starts right after its date;
        // "daily" and "monthly" tick every hour: the command keeps the campaigns
        // whose date hour (and day of the month) match the current time.
        $schedule->command('app:campaign --frequency=once')->everyMinute()->withoutOverlapping(10)->runInBackground();
        $schedule->command('app:campaign --frequency=every_fifteen_minutes')->everyFifteenMinutes()->withoutOverlapping(30)->runInBackground();
        $schedule->command('app:campaign --frequency=every_thirty_minutes')->everyThirtyMinutes()->withoutOverlapping(60)->runInBackground();
        $schedule->command('app:campaign --frequency=hourly')->hourly()->withoutOverlapping(120)->runInBackground();
        $schedule->command('app:campaign --frequency=daily')->hourly()->withoutOverlapping(120)->runInBackground();
        $schedule->command('app:campaign --frequency=monthly')->hourly()->withoutOverlapping(120)->runInBackground();

        // Workflows: resume the waiting executions that are due and recover the stuck ones.
        // Two indexed queries, whatever the number of prospects: it never scans the CRM data.
        $schedule->command('workflows:resume')->everyMinute()->withoutOverlapping(5)->runInBackground();
        // Old history, by batches, at night
        $schedule->command('workflows:prune')->dailyAt('3:30')->withoutOverlapping(60)->runInBackground();

        // Google Sheets instant sync: recover rows left behind by a crash / restart
        $schedule->command('sheet-sync:recover')->everyMinute()->withoutOverlapping();
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
