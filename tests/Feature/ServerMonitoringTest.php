<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Monitoring\RedisMetrics;
use App\Services\Monitoring\ServerMonitoring;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ServerMonitoringTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/heroes-monitoring-api-'.bin2hex(random_bytes(6));
        mkdir($this->directory, 0770, true);
        config([
            'server-monitoring.directory' => $this->directory,
            'database.default' => 'monitoring_must_not_query_database',
            'logging.default' => 'monitoring_test',
            'logging.channels.monitoring_test' => ['driver' => 'single', 'path' => $this->directory.'/app.log'],
        ]);
        Event::fake([Authenticated::class]);
        file_put_contents($this->directory.'/app.log', "[2026-10-28 10:00:00] testing.ERROR: secret-token context must never be returned\n");
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_guest_and_ordinary_user_cannot_collect_metrics(): void
    {
        $service = Mockery::mock(ServerMonitoring::class);
        $service->shouldNotReceive('snapshot');
        $this->app->instance(ServerMonitoring::class, $service);
        $this->getJson('/api/server-monitoring')->assertUnauthorized();
        $this->actingAs($this->user(null))->getJson('/api/server-monitoring')->assertForbidden();
        $this->actingAs($this->user('admin'))->getJson('/api/server-monitoring')->assertForbidden();
    }

    public function test_super_admin_gets_private_response_without_resetting_the_current_project(): void
    {
        $service = Mockery::mock(ServerMonitoring::class);
        $service->shouldReceive('snapshot')->once()->andReturn(['collected_at' => gmdate('c')]);
        $this->app->instance(ServerMonitoring::class, $service);
        $user = $this->user('super_admin');
        $response = $this->actingAs($user)->getJson('/api/server-monitoring')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame(42, $user->last_project_id);
    }

    public function test_real_snapshot_reports_missing_collector_and_caches_diagnostics(): void
    {
        $redis = Mockery::mock(RedisMetrics::class);
        $redis->shouldReceive('collect')->once()->andReturn(['available' => false, 'reason' => 'Redis indisponible.']);
        $this->app->instance(RedisMetrics::class, $redis);
        $this->actingAs($this->user('super_admin'));
        $response = $this->getJson('/api/server-monitoring')->assertOk()
            ->assertJsonPath('system.host.available', false)
            ->assertJsonPath('system.docker.available', false)
            ->assertJsonPath('redis.available', false)
            ->assertJsonPath('laravel.failed_jobs.count', 0)
            ->assertJsonPath('laravel.logs.available', true);
        $this->assertStringNotContainsString('secret-token', $response->getContent());
        $this->assertContains('redis', array_column($response->json('alerts'), 'key'));
        $this->getJson('/api/server-monitoring')->assertOk();
    }

    public function test_monitoring_ignores_project_parameters_and_does_not_update_presence(): void
    {
        $service = Mockery::mock(ServerMonitoring::class);
        $service->shouldReceive('snapshot')->once()->andReturn([]);
        $this->app->instance(ServerMonitoring::class, $service);
        $user = $this->user('super_admin');
        $user->exists = true; // Any accidental Eloquent write now fails on the forbidden connection.
        $this->actingAs($user)->getJson('/api/server-monitoring?project=unknown&prospect=123&user=123&role=abc')->assertOk();
        $this->assertNull($user->last_activity);
        $this->assertSame(42, $user->last_project_id);
    }

    public function test_disabled_telemetry_does_not_publish_previously_cached_values(): void
    {
        config(['server-monitoring.telemetry_enabled' => false]);
        file_put_contents($this->directory.'/diagnostics.json', json_encode([
            'timestamp' => time(), 'redis' => ['available' => true],
            'laravel' => ['logs' => ['available' => true], 'failed_jobs' => ['available' => true, 'count' => 11]],
        ]));
        $this->actingAs($this->user('super_admin'))->getJson('/api/server-monitoring')
            ->assertOk()->assertJsonPath('http.available', false)
            ->assertJsonPath('laravel.failed_jobs.available', false)
            ->assertJsonPath('laravel.failed_jobs.count', null);
    }

    public function test_fresh_values_generate_alerts_and_stale_values_are_not_reported_as_current(): void
    {
        file_put_contents($this->directory.'/app.log', '['.now()->format('Y-m-d H:i:s')."] testing.ERROR: secret-token\n");
        $redis = Mockery::mock(RedisMetrics::class);
        $redis->shouldReceive('collect')->once()->andReturn(['available' => true]);
        $this->app->instance(RedisMetrics::class, $redis);
        $system = [
            'collected_at' => gmdate('c'),
            'host' => ['available' => true, 'cpu' => ['usage_percent' => 92],
                'memory' => ['usage_percent' => 30], 'disk' => ['usage_percent' => 95]],
            'docker' => ['available' => true, 'containers' => [
                ['id' => 'worker', 'name' => 'worker', 'state' => 'restarting', 'status' => 'Restarting (1)'],
                ['id' => 'nginx', 'name' => 'nginx', 'state' => 'exited', 'status' => 'Exited (0)'],
                ['id' => 'certbot', 'name' => 'certbot', 'state' => 'exited', 'status' => 'Exited (0)'],
                ['id' => 'old-project', 'name' => 'old-project', 'state' => 'exited', 'status' => 'Exited (255) 3 weeks ago'],
            ]],
        ];
        file_put_contents($this->directory.'/system.json', json_encode($system));
        $this->actingAs($this->user('super_admin'));
        $response = $this->getJson('/api/server-monitoring')->assertOk()->assertJsonPath('system.host.cpu.usage_percent', 92);
        $keys = array_column($response->json('alerts'), 'key');
        $this->assertContains('host_cpu', $keys);
        $this->assertContains('host_disk', $keys);
        $this->assertContains('container_worker', $keys);
        $this->assertContains('container_nginx', $keys);
        $this->assertNotContains('container_certbot', $keys);
        $this->assertNotContains('container_old-project', $keys);
        $this->assertContains('laravel_errors', $keys);
        $this->assertNotContains('host_memory', $keys);

        $system['collected_at'] = gmdate('c', time() - 60);
        file_put_contents($this->directory.'/system.json', json_encode($system));
        clearstatcache();
        $this->getJson('/api/server-monitoring')->assertOk()
            ->assertJsonPath('system.host.available', false)
            ->assertJsonMissingPath('system.host.cpu')
            ->assertJsonPath('system.docker.containers', []);
    }

    private function user(?string $role): User
    {
        return (new User())->forceFill(['id' => 7, 'name' => 'Monitor', 'role' => $role, 'last_project_id' => 42]);
    }
}
