<?php

namespace Tests\Unit\Monitoring;

use App\Services\Monitoring\LaravelMetrics;
use App\Services\Monitoring\TelemetryStore;
use Mockery;
use Tests\TestCase;

class LaravelMetricsTest extends TestCase
{
    public function test_logs_expose_levels_and_exception_classes_without_messages_or_context(): void
    {
        $directory = sys_get_temp_dir().'/heroes-monitoring-logs-'.bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        $path = $directory.'/laravel.log';
        file_put_contents($path, "[2026-10-28 12:00:00] local.INFO: secret=email@example.com token=private\n[2026-10-28 12:00:01] local.ERROR: password=private {\"exception\":\"[object] (RuntimeException(code: 0): secret at /private/file:10)\"}\n#0 secret stack trace\n");
        config(['logging.default' => 'monitoring-test', 'logging.channels.monitoring-test' => ['driver' => 'single', 'path' => $path]]);
        try {
            $metrics = (new LaravelMetrics(new TelemetryStore($directory)))->logs();
            self::assertTrue($metrics['available']);
            self::assertCount(2, $metrics['entries']);
            self::assertSame('error', $metrics['entries'][0]['level']);
            self::assertSame('Exception : RuntimeException', $metrics['entries'][0]['message']);
            self::assertStringNotContainsString('private', json_encode($metrics));
            self::assertStringNotContainsString('email@example.com', json_encode($metrics));
            self::assertStringNotContainsString('secret', json_encode($metrics));
        } finally {
            unlink($path);
            rmdir($directory);
        }
    }

    public function test_remote_log_channels_are_explicitly_unavailable(): void
    {
        config(['logging.default' => 'stderr']);
        $metrics = (new LaravelMetrics(new TelemetryStore('/tmp/unused-monitoring')))->logs();
        self::assertFalse($metrics['available']);
        self::assertSame([], $metrics['entries']);
    }

    public function test_disabled_telemetry_never_initializes_or_reads_failure_storage(): void
    {
        config(['server-monitoring.telemetry_enabled' => false, 'logging.default' => 'stderr']);
        $store = Mockery::mock(TelemetryStore::class);
        $store->shouldNotReceive('initializeFailures');
        $store->shouldNotReceive('failedJobsSnapshot');
        $metrics = (new LaravelMetrics($store))->snapshot();

        self::assertFalse($metrics['failed_jobs']['available']);
        self::assertNull($metrics['failed_jobs']['count']);
        self::assertSame([], $metrics['failed_jobs']['entries']);
        self::assertStringContainsString('désactivée', $metrics['failed_jobs']['reason']);
    }
}
