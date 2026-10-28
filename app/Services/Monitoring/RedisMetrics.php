<?php

namespace App\Services\Monitoring;

use Illuminate\Redis\RedisManager;
use Illuminate\Support\ConfigurationUrlParser;
use Throwable;

class RedisMetrics
{
    public function collect(): array
    {
        $connection = null;
        try {
            // Separate, bounded connection; never change timeouts on the CRM's client.
            $manager = new RedisManager(app(), config('database.redis.client', 'phpredis'), $this->connectionConfiguration());
            $connection = $manager->connection();
            $started = hrtime(true);
            $connection->ping();
            $latency = (hrtime(true) - $started) / 1e6;
            $info = $connection->info('memory');
            // Predis and PhpRedis may return flat or sectioned INFO arrays.
            $memory = $info['Memory'] ?? $info;

            return [
                'available' => true, 'reason' => null,
                'used_memory_bytes' => isset($memory['used_memory']) ? (int) $memory['used_memory'] : null,
                'maxmemory_bytes' => isset($memory['maxmemory']) ? (int) $memory['maxmemory'] : null,
                'latency_ms' => round($latency, 2),
            ];
        } catch (Throwable $e) {
            return ['available' => false, 'reason' => 'Redis inaccessible ou collecte non autorisée.',
                'used_memory_bytes' => null, 'maxmemory_bytes' => null, 'latency_ms' => null];
        } finally {
            try {
                $connection?->disconnect();
            } catch (Throwable $e) {
                // Diagnostics must not affect the application.
            }
        }
    }

    protected function connectionConfiguration(): array
    {
        // Resolve REDIS_URL first: its query parameters normally override array settings.
        $settings = (new ConfigurationUrlParser)->parseConfiguration(config('database.redis.default', []));
        if (in_array($settings['driver'] ?? '', ['tcp', 'tls'], true)) {
            $settings['scheme'] = $settings['driver'];
        }
        unset($settings['driver']);

        return [
            'default' => $this->boundedOptions($settings),
            'options' => $this->boundedOptions(config('database.redis.options', [])),
        ];
    }

    private function boundedOptions(array $options): array
    {
        // PhpRedis merges global/nested options after the connection config. Predis
        // also supports parameter defaults, and calls its read limit read_write_timeout.
        foreach (['options', 'parameters', 'default'] as $key) {
            if (isset($options[$key]) && is_array($options[$key])) {
                $options[$key] = $this->boundedOptions($options[$key]);
            }
        }

        return array_replace($options, [
            'timeout' => 0.3, 'read_timeout' => 0.3, 'read_write_timeout' => 0.3,
            'retry_interval' => 0, 'persistent' => false, 'max_retries' => 0,
        ]);
    }
}
