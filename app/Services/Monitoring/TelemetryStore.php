<?php

namespace App\Services\Monitoring;

use Throwable;

/** Bounded, best-effort telemetry: no database, Redis dependency or blocking locks. */
class TelemetryStore
{
    private const BUCKET_SECONDS = 5;
    private const MAX_ENDPOINTS = 32;
    private const MAX_BYTES = 262144;
    private const LATENCY_BOUNDS = [5, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000, 30000];

    public function __construct(private string $directory, private int $windowSeconds = 60)
    {
        $this->windowSeconds = max(10, min(300, $windowSeconds));
    }

    public function recordRequest(string $method, string $route, int $status, float $durationMs, ?float $now = null): void
    {
        $now ??= microtime(true);
        // Callers must supply Laravel's route template, never the actual request URL.
        $endpoint = substr(strtoupper($method).' /'.ltrim($route, '/'), 0, 200);
        $this->mutate('http', function (array $state) use ($endpoint, $status, $durationMs, $now): array {
            $state['since'] ??= $now;
            $state['buckets'] = $this->recentBuckets($state['buckets'] ?? [], $now);
            $key = (string) (floor($now / self::BUCKET_SECONDS) * self::BUCKET_SECONDS);
            $bucket = $state['buckets'][$key] ?? ['count' => 0, 'duration' => 0, '4xx' => 0, '5xx' => 0, 'histogram' => [], 'endpoints' => []];
            $durationMs = max(0, min(3600000, $durationMs));
            $bucket['count']++;
            $bucket['duration'] += $durationMs;
            $bucket['4xx'] += (int) ($status >= 400 && $status < 500);
            $bucket['5xx'] += (int) ($status >= 500);
            $latencyIndex = count(self::LATENCY_BOUNDS);
            foreach (self::LATENCY_BOUNDS as $index => $bound) {
                if ($durationMs <= $bound) {
                    $latencyIndex = $index;
                    break;
                }
            }
            $bucket['histogram'][$latencyIndex] = ($bucket['histogram'][$latencyIndex] ?? 0) + 1;
            $endpointKey = $endpoint;
            if (!isset($bucket['endpoints'][$endpointKey]) && count($bucket['endpoints']) >= self::MAX_ENDPOINTS) {
                $endpointKey = 'OTHER /[autres routes]';
            }
            $entry = $bucket['endpoints'][$endpointKey] ?? ['count' => 0, 'duration' => 0];
            $entry['count']++;
            $entry['duration'] += $durationMs;
            $bucket['endpoints'][$endpointKey] = $entry;
            $state['buckets'][$key] = $bucket;
            return $state;
        });
    }

    public function httpSnapshot(?float $now = null): array
    {
        $now ??= microtime(true);
        $state = $this->read('http');
        if ($state === null) {
            return ['available' => false, 'reason' => 'Aucune requête collectée ou stockage de télémétrie inaccessible.'];
        }
        $count = $duration = $errors4xx = $errors5xx = 0;
        $histogram = $endpoints = [];
        foreach ($this->recentBuckets($state['buckets'] ?? [], $now) as $bucket) {
            $count += $bucket['count'];
            $duration += $bucket['duration'];
            $errors4xx += $bucket['4xx'];
            $errors5xx += $bucket['5xx'];
            foreach ($bucket['histogram'] as $index => $number) {
                $histogram[$index] = ($histogram[$index] ?? 0) + $number;
            }
            foreach ($bucket['endpoints'] as $key => $entry) {
                $endpoints[$key]['count'] = ($endpoints[$key]['count'] ?? 0) + $entry['count'];
                $endpoints[$key]['duration'] = ($endpoints[$key]['duration'] ?? 0) + $entry['duration'];
            }
        }
        uasort($endpoints, fn ($a, $b) => $b['count'] <=> $a['count']);
        $top = [];
        foreach (array_slice($endpoints, 0, 10, true) as $key => $entry) {
            [$method, $path] = explode(' ', $key, 2);
            $top[] = ['method' => $method, 'path' => $path, 'count' => $entry['count'], 'average_response_ms' => round($entry['duration'] / $entry['count'], 2)];
        }
        ksort($histogram, SORT_NUMERIC);
        $cumulative = 0;
        $p95 = null;
        foreach ($histogram as $index => $number) {
            $cumulative += $number;
            if ($cumulative >= ceil($count * .95)) {
                $p95 = self::LATENCY_BOUNDS[$index] ?? null;
                break;
            }
        }
        $elapsed = max(1, min($this->windowSeconds, $now - ($state['since'] ?? $now)));
        return [
            'available' => true, 'window_seconds' => $this->windowSeconds,
            'bucket_seconds' => self::BUCKET_SECONDS, 'count' => $count,
            'requests_per_second' => round($count / $elapsed, 2),
            'average_response_ms' => $count ? round($duration / $count, 2) : null,
            'p95_response_ms' => $count ? $p95 : null, 'p95_approximate' => true,
            'p95_overflow' => $count > 0 && $p95 === null,
            'errors_4xx' => $errors4xx, 'errors_5xx' => $errors5xx, 'endpoints' => $top,
            'scope' => 'Requêtes traitées par Laravel, hors monitoring. Collecte locale au serveur, au mieux, par tranches de 5 s.',
        ];
    }

    public function initializeFailures(): void
    {
        if ($this->read('failures') !== null) {
            return;
        }
        $this->mutate('failures', fn (array $state) => $state + ['since' => gmdate('c'), 'events' => [], 'buckets' => []]);
    }

    public function recordFailedJob(string $connection, string $queue, string $exceptionClass, ?int $now = null): void
    {
        $now ??= time();
        $this->mutate('failures', function (array $state) use ($connection, $queue, $exceptionClass, $now): array {
            $state['since'] ??= gmdate('c', $now);
            $state['buckets'] = array_filter($state['buckets'] ?? [], fn ($key) => (int) $key >= $now - 86400, ARRAY_FILTER_USE_KEY);
            $minute = (string) (intdiv($now, 60) * 60);
            $state['buckets'][$minute] = ($state['buckets'][$minute] ?? 0) + 1;
            $state['events'] = array_values(array_filter($state['events'] ?? [], fn ($event) => $event['at'] >= $now - 86400));
            $state['events'][] = [
                'at' => $now, 'timestamp' => gmdate('c', $now),
                'connection' => $this->safeIdentifier($connection), 'queue' => $this->safeIdentifier($queue),
                'exception' => $this->safeIdentifier($exceptionClass),
            ];
            $state['events'] = array_slice($state['events'], -30);
            return $state;
        });
    }

    public function failedJobsSnapshot(?int $now = null): array
    {
        $now ??= time();
        $state = $this->read('failures');
        if ($state === null) {
            return ['available' => false, 'reason' => 'Le registre local des jobs échoués est inaccessible.'];
        }
        $buckets = array_filter($state['buckets'] ?? [], fn ($key) => (int) $key >= $now - 86400, ARRAY_FILTER_USE_KEY);
        $events = array_values(array_filter($state['events'] ?? [], fn ($event) => $event['at'] >= $now - 86400));
        foreach ($events as &$event) {
            unset($event['at']);
        }
        return [
            'available' => true, 'count' => array_sum($buckets), 'entries' => array_reverse($events),
            'since' => $state['since'] ?? null, 'window_seconds' => 86400,
            'scope' => 'Événements JobFailed observés sur 24 h depuis activation ; les anciens failed_jobs ne sont pas interrogés.',
        ];
    }

    private function safeIdentifier(string $value): string
    {
        return substr(preg_replace('/[^a-zA-Z0-9_\\\\.:-]/', '_', $value), 0, 160);
    }

    private function recentBuckets(array $buckets, float $now): array
    {
        $cutoff = floor($now / self::BUCKET_SECONDS) * self::BUCKET_SECONDS - $this->windowSeconds + self::BUCKET_SECONDS;
        return array_filter($buckets, fn ($key) => (float) $key >= $cutoff && (float) $key <= $now, ARRAY_FILTER_USE_KEY);
    }

    private function mutate(string $name, callable $mutator): void
    {
        $handle = null;
        try {
            if (!is_dir($this->directory) && !@mkdir($this->directory, 0770, true) && !is_dir($this->directory)) {
                return;
            }
            $path = $this->directory.'/'.$name.'.json';
            $handle = @fopen($path, 'c+');
            if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
                return;
            }
            $raw = stream_get_contents($handle, self::MAX_BYTES + 1);
            $state = strlen($raw) <= self::MAX_BYTES ? json_decode($raw, true) : null;
            $encoded = json_encode($mutator(is_array($state) ? $state : []), JSON_INVALID_UTF8_SUBSTITUTE);
            if ($encoded !== false && strlen($encoded) <= self::MAX_BYTES) {
                rewind($handle);
                fwrite($handle, $encoded);
                ftruncate($handle, strlen($encoded));
                fflush($handle);
                @chmod($path, 0660);
            }
        } catch (Throwable $exception) {
            // Telemetry must never prevent a response or fail a queue worker.
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    private function read(string $name): ?array
    {
        $handle = null;
        try {
            $handle = @fopen($this->directory.'/'.$name.'.json', 'r');
            if (!$handle || !flock($handle, LOCK_SH | LOCK_NB)) {
                return null;
            }
            $raw = stream_get_contents($handle, self::MAX_BYTES + 1);
            $state = strlen($raw) <= self::MAX_BYTES ? json_decode($raw, true) : null;
            return is_array($state) ? $state : null;
        } catch (Throwable $exception) {
            return null;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }
}
