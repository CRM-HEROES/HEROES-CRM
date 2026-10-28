<?php

namespace Tests\Unit\Monitoring;

use App\Services\Monitoring\RedisMetrics;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use Tests\TestCase;

class RedisMetricsTest extends TestCase
{
    private function service(): RedisMetrics
    {
        return new class extends RedisMetrics {
            public function configurationForTest(): array
            {
                return $this->connectionConfiguration();
            }
        };
    }

    public function test_phpredis_final_options_remain_bounded_after_url_and_connector_merges(): void
    {
        if (!class_exists(\Redis::class)) {
            self::markTestSkipped('PhpRedis is not installed.');
        }
        config([
            'database.redis.default' => [
                'url' => 'rediss://monitor:fixture-password@redis.example:6380/2?timeout=900&read_timeout=900&persistent=true',
                'options' => ['timeout' => 800, 'read_timeout' => 800, 'persistent' => true, 'context' => ['stream' => ['verify_peer' => true]]],
            ],
            'database.redis.options' => ['timeout' => 700, 'read_timeout' => 700, 'retry_interval' => 700, 'persistent' => true],
        ]);
        $config = $this->service()->configurationForTest();
        $connector = new class extends PhpRedisConnector {
            public array $effective = [];

            protected function createClient(array $config)
            {
                $this->effective = $config;
                return new \Redis;
            }
        };
        // Exercise the installed Laravel connector's actual precedence rules without connecting.
        $connector->connect($config['default'], $config['options']);
        self::assertSame(.3, $connector->effective['timeout']);
        self::assertSame(.3, $connector->effective['read_timeout']);
        self::assertSame(0, $connector->effective['retry_interval']);
        self::assertFalse($connector->effective['persistent']);
        self::assertSame('tls', $connector->effective['scheme']);
        self::assertSame('monitor', $connector->effective['username']);
        self::assertSame('fixture-password', $connector->effective['password']);
        self::assertSame('redis.example', $connector->effective['host']);
        self::assertSame('2', $connector->effective['database']);
        self::assertTrue($connector->effective['context']['stream']['verify_peer']);
    }

    public function test_predis_read_limits_override_all_parameter_default_layers(): void
    {
        config([
            'database.redis.default' => [
                'url' => 'redis://localhost:6379?read_write_timeout=900&timeout=900',
                'options' => ['parameters' => ['read_write_timeout' => 800]],
            ],
            'database.redis.options' => [
                'parameters' => ['read_write_timeout' => 700, 'default' => ['read_write_timeout' => 600, 'persistent' => true]],
            ],
        ]);
        $config = $this->service()->configurationForTest();

        self::assertArrayNotHasKey('url', $config['default']);
        self::assertSame(.3, $config['default']['read_write_timeout']);
        self::assertSame(.3, $config['default']['options']['parameters']['read_write_timeout']);
        self::assertSame(.3, $config['options']['parameters']['read_write_timeout']);
        self::assertSame(.3, $config['options']['parameters']['default']['read_write_timeout']);
        self::assertFalse($config['options']['parameters']['default']['persistent']);
    }
}
