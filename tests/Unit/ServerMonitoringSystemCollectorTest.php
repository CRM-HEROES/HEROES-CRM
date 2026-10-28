<?php

namespace Tests\Unit;

use App\Services\Monitoring\SystemCollector;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class ServerMonitoringSystemCollectorTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/heroes-monitoring-'.bin2hex(random_bytes(8));
        foreach (['proc/sys/kernel/random', 'proc/1/net', 'sys/class/net/eth0', 'root'] as $path) {
            mkdir($this->directory.'/'.$path, 0750, true);
        }
        file_put_contents($this->directory.'/proc/sys/kernel/random/boot_id', 'boot-one');
        file_put_contents($this->directory.'/proc/stat', "cpu 100 0 50 850 0 0 0 0 0 0\ncpu0 50 0 25 425\ncpu1 50 0 25 425\n");
        file_put_contents($this->directory.'/proc/meminfo', "MemTotal: 1000 kB\nMemAvailable: 400 kB\n");
        file_put_contents($this->directory.'/proc/loadavg', '1.00 0.75 0.50 1/100 100');
        file_put_contents($this->directory.'/proc/uptime', '12345.50 1000');
        file_put_contents($this->directory.'/sys/class/net/eth0/speed', '1000');
        file_put_contents($this->directory.'/sys/class/net/eth0/ifindex', '2');
        $this->network(1000, 2000);
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function collector(): SystemCollector
    {
        return new SystemCollector([
            'directory' => $this->directory,
            'host_proc' => $this->directory.'/proc',
            'host_sys' => $this->directory.'/sys',
            'host_root' => $this->directory.'/root',
            'docker_socket' => $this->directory.'/missing.sock',
        ]);
    }

    private function network(int $rx, int $tx): void
    {
        file_put_contents($this->directory.'/proc/1/net/dev', "Inter-| Receive | Transmit\n eth0: $rx 0 0 0 0 0 0 0 $tx 0 0 0 0 0 0 0\n lo: 999 0 0 0 0 0 0 0 999 0 0 0 0 0 0 0\n vethabc: 999 0 0 0 0 0 0 0 999 0 0 0 0 0 0 0\n");
    }

    public function test_first_sample_is_unknown_and_linux_counters_are_not_confused_with_deltas(): void
    {
        $snapshot = $this->collector()->collect();
        $this->assertTrue($snapshot['host']['available']);
        $this->assertNull($snapshot['host']['cpu']['usage_percent']);
        $this->assertSame(2, $snapshot['host']['cpu']['cores']);
        $this->assertSame([1.0, 0.75, 0.5], $snapshot['host']['cpu']['load']);
        $this->assertSame(12345.5, $snapshot['host']['cpu']['uptime_seconds']);
        $this->assertSame(1024000, $snapshot['host']['memory']['total_bytes']);
        $this->assertSame(614400, $snapshot['host']['memory']['used_bytes']);
        $this->assertSame(60.0, $snapshot['host']['memory']['usage_percent']);
        $this->assertNull($snapshot['host']['network']['rx_bytes_per_second']);
        $this->assertCount(1, $snapshot['host']['network']['interfaces']);
        $this->assertFalse($snapshot['docker']['available']);
    }

    public function test_cpu_and_network_use_real_counter_deltas_and_reset_after_reboot(): void
    {
        $collector = $this->collector();
        $collector->collect();
        $property = new ReflectionProperty($collector, 'previous');
        $previous = $property->getValue($collector);
        $previous['time'] = microtime(true) - 5;
        $property->setValue($collector, $previous);
        file_put_contents($this->directory.'/proc/stat', "cpu 150 0 100 950 0 0 0 0\ncpu0 1\ncpu1 1\n");
        $this->network(1500, 3000);
        $snapshot = $collector->collect();
        $this->assertSame(50.0, $snapshot['host']['cpu']['usage_percent']);
        $this->assertEqualsWithDelta(100, $snapshot['host']['network']['rx_bytes_per_second'], 1);
        $this->assertEqualsWithDelta(200, $snapshot['host']['network']['tx_bytes_per_second'], 1);
        file_put_contents($this->directory.'/proc/sys/kernel/random/boot_id', 'boot-two');
        $snapshot = $collector->collect();
        $this->assertNull($snapshot['host']['cpu']['usage_percent']);
        $this->assertNull($snapshot['host']['network']['rx_bytes_per_second']);
    }

    public function test_snapshots_are_published_as_complete_json_and_private_state_is_separate(): void
    {
        $collector = $this->collector();
        $snapshot = $collector->collectAndStore();
        $published = json_decode(file_get_contents($this->directory.'/system.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($snapshot, $published);
        $this->assertArrayNotHasKey('boot_id', $published);
        $this->assertFileExists($this->directory.'/.system-state.json');
        $this->assertSame(0640, fileperms($this->directory.'/system.json') & 0777);
    }

    public function test_docker_cache_memory_and_multicore_cpu_are_calculated_from_counters(): void
    {
        $collector = $this->collector();
        $property = new ReflectionProperty($collector, 'previous');
        $property->setValue($collector, ['time' => microtime(true) - 5, 'docker' => ['test' => ['total' => 100, 'system' => 1000]]]);
        $method = new ReflectionMethod($collector, 'dockerStats');
        $stats = $method->invoke($collector, 'test', [
            'read' => '2026-10-28T10:00:00Z',
            'cpu_stats' => ['cpu_usage' => ['total_usage' => 300], 'system_cpu_usage' => 2000, 'online_cpus' => 8],
            'memory_stats' => ['usage' => 1000, 'limit' => 2000, 'stats' => ['inactive_file' => 400]],
        ]);
        $this->assertSame(160.0, $stats['cpu_percent']);
        $this->assertSame(600, $stats['memory_bytes']);
        $this->assertSame(30.0, $stats['memory_percent']);
    }

    public function test_database_containers_are_excluded_before_stats_collection(): void
    {
        $collector = $this->collector();
        $method = new ReflectionMethod($collector, 'databaseContainer');
        $this->assertTrue($method->invoke($collector, ['Image' => 'mysql:8.0', 'Names' => ['/heroes-crm-mysql']]));
        $this->assertTrue($method->invoke($collector, ['Image' => 'postgres:15', 'Names' => ['/db']]));
        $this->assertFalse($method->invoke($collector, ['Image' => 'redis:alpine', 'Names' => ['/redis-heroes']]));
        $this->assertFalse($method->invoke($collector, ['Image' => 'heroescrm:latest', 'Names' => ['/heroescrm']]));
    }

    public function test_docker_limits_prioritize_running_services_and_never_request_database_stats(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('The production Docker collector requires cURL.');
        }
        file_put_contents($this->directory.'/socket-fixture', '');
        $options = [
            'host_proc' => $this->directory.'/proc', 'host_sys' => $this->directory.'/sys',
            'host_root' => $this->directory.'/root', 'docker_socket' => $this->directory.'/socket-fixture',
            'docker_max_containers' => 1,
        ];
        $collector = new class($options) extends SystemCollector {
            public Client $testClient;

            protected function dockerClient(string $socket, float $timeout): Client
            {
                return $this->testClient;
            }
        };
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                ['Id' => str_repeat('a', 64), 'Image' => 'nginx:latest', 'Names' => ['/old-stopped'], 'State' => 'exited'],
                ['Id' => str_repeat('b', 64), 'Image' => 'mysql:8.0', 'Names' => ['/database'], 'State' => 'running'],
                ['Id' => str_repeat('c', 64), 'Image' => 'heroescrm:latest', 'Names' => ['/heroescrm'], 'State' => 'running'],
            ])),
            new Response(200, [], json_encode(['memory_stats' => ['usage' => 800, 'limit' => 1000, 'stats' => ['inactive_file' => 300]]])),
        ]);
        $history = [];
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($history));
        $collector->testClient = new Client(['handler' => $handler]);
        $snapshot = $collector->collect();
        $this->assertTrue($snapshot['docker']['available']);
        $this->assertTrue($snapshot['docker']['truncated']);
        $this->assertSame(2, $snapshot['docker']['total_containers']);
        $this->assertSame('heroescrm', $snapshot['docker']['containers'][0]['name']);
        $this->assertSame(500, $snapshot['docker']['containers'][0]['memory_bytes']);
        $this->assertCount(2, $history);
        $this->assertSame('/containers/json', $history[0]['request']->getUri()->getPath());
        parse_str($history[0]['request']->getUri()->getQuery(), $inventoryQuery);
        $this->assertSame(['all' => '1'], $inventoryQuery);
        $this->assertSame('/containers/'.str_repeat('c', 64).'/stats', $history[1]['request']->getUri()->getPath());
        parse_str($history[1]['request']->getUri()->getQuery(), $query);
        $this->assertSame(['stream' => 'false', 'one-shot' => 'true'], $query);
    }

    public function test_docker_cpu_recovers_from_real_counters_across_a_failed_inventory_sample(): void
    {
        $id = str_repeat('c', 64);
        $inventory = json_encode([['Id' => $id, 'Image' => 'heroescrm:latest', 'Names' => ['/heroescrm'], 'State' => 'running']]);
        $stats = fn ($total, $system) => json_encode([
            'read' => '2026-10-28T10:00:00Z',
            'cpu_stats' => ['cpu_usage' => ['total_usage' => $total], 'system_cpu_usage' => $system, 'online_cpus' => 8],
        ]);
        $collector = $this->mockedDockerCollector([
            new Response(200, [], $inventory), new Response(200, [], $stats(100, 1000)),
            new ConnectException('Timed out', new Request('GET', '/containers/json')),
            new Response(200, [], $inventory), new Response(200, [], $stats(300, 2000)),
        ]);
        $first = $collector->collect();
        $this->assertNull($first['docker']['containers'][0]['cpu_percent']);
        $property = new ReflectionProperty(SystemCollector::class, 'previous');
        $baseline = $property->getValue($collector);
        $baseline['docker'][$id]['time'] = microtime(true) - 10;
        $property->setValue($collector, $baseline);
        $failed = $collector->collect();
        $this->assertFalse($failed['docker']['available']);
        $this->assertSame([], $failed['docker']['containers']);
        $retained = $property->getValue($collector)['docker'][$id];
        $this->assertSame(100, $retained['total']);
        $this->assertSame($baseline['docker'][$id]['time'], $retained['time']);
        $recovered = $collector->collect();
        $this->assertTrue($recovered['docker']['available']);
        $this->assertSame(160.0, $recovered['docker']['containers'][0]['cpu_percent']);
    }

    public function test_failed_samples_do_not_extend_the_lifetime_of_old_docker_counters(): void
    {
        $collector = $this->collector();
        $property = new ReflectionProperty($collector, 'previous');
        $property->setValue($collector, [
            'time' => microtime(true) - 1,
            'docker' => ['expired' => ['total' => 100, 'system' => 1000, 'time' => microtime(true) - 61]],
        ]);
        $snapshot = $collector->collect();
        $this->assertFalse($snapshot['docker']['available']);
        $this->assertSame([], $property->getValue($collector)['docker']);
    }

    public function test_optional_project_scope_is_filtered_by_the_daemon_and_includes_stopped_containers(): void
    {
        $collector = $this->mockedDockerCollector([new Response(200, [], '[]')], ['docker_project' => 'heroes-crm']);
        $history = [];
        $collector->testClient->getConfig('handler')->push(Middleware::history($history));
        $snapshot = $collector->collect();
        $this->assertTrue($snapshot['docker']['available']);
        $this->assertSame([], $snapshot['docker']['containers']);
        $this->assertCount(1, $history);
        parse_str($history[0]['request']->getUri()->getQuery(), $query);
        $this->assertSame('1', $query['all']);
        $this->assertSame(['label' => ['com.docker.compose.project=heroes-crm']], json_decode($query['filters'], true, 512, JSON_THROW_ON_ERROR));
    }

    private function mockedDockerCollector(array $responses, array $overrides = []): SystemCollector
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('The production Docker collector requires cURL.');
        }
        file_put_contents($this->directory.'/socket-fixture', '');
        $options = array_replace([
            'host_proc' => $this->directory.'/proc', 'host_sys' => $this->directory.'/sys',
            'host_root' => $this->directory.'/root', 'docker_socket' => $this->directory.'/socket-fixture',
        ], $overrides);
        $collector = new class($options) extends SystemCollector {
            public Client $testClient;

            protected function dockerClient(string $socket, float $timeout): Client
            {
                return $this->testClient;
            }
        };
        $collector->testClient = new Client(['handler' => HandlerStack::create(new MockHandler($responses))]);

        return $collector;
    }
}
