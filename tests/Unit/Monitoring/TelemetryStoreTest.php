<?php

namespace Tests\Unit\Monitoring;

use App\Services\Monitoring\TelemetryStore;
use PHPUnit\Framework\TestCase;

class TelemetryStoreTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/heroes-monitoring-test-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
    }

    public function test_http_metrics_aggregate_statuses_latency_and_expire(): void
    {
        $store = new TelemetryStore($this->directory);
        $store->recordRequest('GET', 'api/project/{project}', 200, 10, 1000);
        $store->recordRequest('GET', 'api/project/{project}', 404, 20, 1001);
        $store->recordRequest('POST', 'api/project', 500, 40, 1002);
        $metrics = $store->httpSnapshot(1005);

        self::assertTrue($metrics['available']);
        self::assertSame(3, $metrics['count']);
        self::assertSame(0.6, $metrics['requests_per_second']);
        self::assertSame(23.33, $metrics['average_response_ms']);
        self::assertSame(1, $metrics['errors_4xx']);
        self::assertSame(1, $metrics['errors_5xx']);
        self::assertSame(50, $metrics['p95_response_ms']);
        self::assertSame('/api/project/{project}', $metrics['endpoints'][0]['path']);
        self::assertSame(0, $store->httpSnapshot(1065)['count']);
    }

    public function test_lock_contention_skips_telemetry_instead_of_waiting(): void
    {
        $store = new TelemetryStore($this->directory);
        $store->recordRequest('GET', 'api/ping', 200, 5, 1000);
        $lock = fopen($this->directory.'/http.json', 'c+');
        flock($lock, LOCK_EX);
        $started = microtime(true);
        $store->recordRequest('GET', 'api/ping', 500, 5, 1001);
        self::assertLessThan(.1, microtime(true) - $started);
        self::assertFalse($store->httpSnapshot(1001)['available']);
        fclose($lock);
        self::assertSame(1, $store->httpSnapshot(1001)['count']);
    }

    public function test_endpoint_cardinality_is_bounded_without_losing_total_count(): void
    {
        $store = new TelemetryStore($this->directory);
        for ($index = 0; $index < 200; $index++) {
            $store->recordRequest('GET', 'api/route-'.$index, 200, 10, 1000);
        }
        $metrics = $store->httpSnapshot(1005);
        self::assertSame(200, $metrics['count']);
        self::assertSame('/[autres routes]', $metrics['endpoints'][0]['path']);
        self::assertLessThan(262144, filesize($this->directory.'/http.json'));
    }

    public function test_failed_jobs_keep_bounded_details_and_24_hour_counts(): void
    {
        $store = new TelemetryStore($this->directory);
        for ($index = 0; $index < 100; $index++) {
            $store->recordFailedJob('redis', 'imports', 'RuntimeException', 100000);
        }
        $metrics = $store->failedJobsSnapshot(100001);
        self::assertSame(100, $metrics['count']);
        self::assertCount(30, $metrics['entries']);
        self::assertSame('RuntimeException', $metrics['entries'][0]['exception']);
        self::assertSame(0, $store->failedJobsSnapshot(186500)['count']);
    }

    public function test_unwritable_storage_does_not_throw(): void
    {
        file_put_contents($this->directory.'/blocked', 'file');
        $store = new TelemetryStore($this->directory.'/blocked/child');
        $store->recordRequest('GET', 'api/ping', 200, 10, 1000);
        $store->recordFailedJob('redis', 'imports', 'RuntimeException');
        self::assertFalse($store->httpSnapshot()['available']);
        self::assertFalse($store->failedJobsSnapshot()['available']);
    }
}
