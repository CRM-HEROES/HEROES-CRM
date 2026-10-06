<?php

namespace App\Console\Commands\Monitoring;

use App\Models\ServerMetric;
use App\Services\Monitoring\ServerMetricsCollector;
use Illuminate\Console\Command;

class Collect extends Command
{
    protected $signature = 'app:monitoring-collect';

    protected $description = 'Store one sample of server metrics (CPU, RAM, disk, network) for the monitoring history';

    public function handle(ServerMetricsCollector $collector): int
    {
        $sample = $collector->collect();

        // First run (no previous counters) or unsupported platform: nothing reliable to store
        if (empty($sample['available']) || empty($sample['cpu']) || $sample['cpu']['percent'] === null || empty($sample['ram'])) {
            return self::SUCCESS;
        }

        ServerMetric::create([
            'recorded_at' => now(),
            'cpu_percent' => $sample['cpu']['percent'],
            'load_1' => $sample['cpu']['load'][0] ?? null,
            'ram_percent' => $sample['ram']['percent'],
            'ram_used' => $sample['ram']['used'],
            'ram_total' => $sample['ram']['total'],
            'disk_percent' => $sample['disk']['percent'] ?? null,
            'net_rx_bps' => $sample['network']['rx_bps'] ?? 0,
            'net_tx_bps' => $sample['network']['tx_bps'] ?? 0,
            'details' => ServerMetricsCollector::compactDetails($sample),
        ]);

        return self::SUCCESS;
    }
}
