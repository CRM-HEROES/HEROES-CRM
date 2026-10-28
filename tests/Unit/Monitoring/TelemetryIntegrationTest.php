<?php

namespace Tests\Unit\Monitoring;

use App\Http\Middleware\RecordServerMonitoringRequest;
use App\Providers\ServerMonitoringServiceProvider;
use App\Services\Monitoring\TelemetryStore;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TelemetryIntegrationTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/heroes-monitoring-integration-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
        config(['server-monitoring.directory' => $this->directory, 'server-monitoring.telemetry_enabled' => true]);
        $this->app->register(ServerMonitoringServiceProvider::class);
        $this->app->forgetInstance(TelemetryStore::class);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_termination_records_final_response_and_route_template_only(): void
    {
        $request = Request::create('/api/project/42?token=secret');
        $request->setRouteResolver(fn () => new Route('GET', 'api/project/{project}', fn () => null));
        $middleware = new RecordServerMonitoringRequest;
        $response = $middleware->handle($request, fn () => new Response('', 503));
        $middleware->terminate($request, $response);
        $metrics = app(TelemetryStore::class)->httpSnapshot();

        self::assertSame(1, $metrics['errors_5xx']);
        self::assertSame('/api/project/{project}', $metrics['endpoints'][0]['path']);
        self::assertStringNotContainsString('secret', json_encode($metrics));
        self::assertStringNotContainsString('42', $metrics['endpoints'][0]['path']);
    }

    public function test_monitoring_requests_are_excluded_from_the_measured_traffic(): void
    {
        $request = Request::create('/api/server-monitoring');
        $middleware = new RecordServerMonitoringRequest;
        $response = $middleware->handle($request, fn () => new Response);
        $middleware->terminate($request, $response);
        self::assertFalse(app(TelemetryStore::class)->httpSnapshot()['available']);
    }

    public function test_queue_failure_event_persists_only_job_metadata_and_exception_class(): void
    {
        $job = $this->createMock(Job::class);
        $job->method('getQueue')->willReturn('imports');
        Event::dispatch(new JobFailed('redis', $job, new \RuntimeException('Secret customer payload')));
        $metrics = app(TelemetryStore::class)->failedJobsSnapshot();

        self::assertSame(1, $metrics['count']);
        self::assertSame('RuntimeException', $metrics['entries'][0]['exception']);
        self::assertSame('imports', $metrics['entries'][0]['queue']);
        self::assertStringNotContainsString('Secret customer payload', json_encode($metrics));
    }
}
