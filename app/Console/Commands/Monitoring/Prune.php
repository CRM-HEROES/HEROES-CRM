<?php

namespace App\Console\Commands\Monitoring;

use App\Models\ServerMetric;
use Illuminate\Console\Command;

class Prune extends Command
{
    protected $signature = 'app:monitoring-prune';

    protected $description = 'Delete server metrics older than the configured retention';

    public function handle(): int
    {
        $deleted = ServerMetric::where('recorded_at', '<', now()->subDays(config('monitoring.retention_days')))->delete();
        $this->info("Deleted {$deleted} server metric rows.");

        return self::SUCCESS;
    }
}
