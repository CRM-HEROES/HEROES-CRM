<?php

namespace App\Services\Monitoring;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class LaravelMetrics
{
    public function __construct(private TelemetryStore $store)
    {
    }

    public function snapshot(): array
    {
        if (!config('server-monitoring.telemetry_enabled', true)) {
            return ['logs' => $this->logs(), 'failed_jobs' => [
                'available' => false, 'count' => null, 'entries' => [],
                'reason' => 'Collecte des jobs échoués désactivée dans la configuration de monitoring.',
            ]];
        }
        $this->store->initializeFailures();
        return ['logs' => $this->logs(), 'failed_jobs' => $this->store->failedJobsSnapshot()];
    }

    public function logs(): array
    {
        try {
            $paths = array_slice(array_unique($this->channelPaths((string) config('logging.default', 'stack'))), 0, 4);
            $entries = [];
            $readable = false;
            foreach ($paths as $path) {
                if (!is_readable($path)) {
                    continue;
                }
                $readable = true;
                foreach ($this->readTail($path) as $entry) {
                    $entries[] = $entry;
                }
            }
            if (!$readable) {
                return ['available' => false, 'reason' => 'Aucun fichier lisible pour le canal de logs Laravel actif (les canaux distants ne sont pas lus).', 'entries' => []];
            }
            usort($entries, fn ($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
            $entries = array_slice($entries, 0, 50);
            return [
                'available' => true, 'entries' => $entries,
                'scope' => '50 dernières entrées de la fin des fichiers actifs (64 Kio maximum par fichier). Messages et contextes masqués ; classes d’exception conservées.',
            ];
        } catch (Throwable $exception) {
            return ['available' => false, 'entries' => [], 'reason' => 'Lecture des logs indisponible.'];
        }
    }

    /** Only inspect files explicitly configured by a local single/daily channel. */
    private function channelPaths(string $channel, int $depth = 0): array
    {
        if ($depth > 3) {
            return [];
        }
        $config = config('logging.channels.'.$channel, []);
        if (($config['driver'] ?? null) === 'stack') {
            $paths = [];
            foreach (array_slice($config['channels'] ?? [], 0, 4) as $child) {
                $paths = array_merge($paths, $this->channelPaths($child, $depth + 1));
            }
            return $paths;
        }
        $path = $config['path'] ?? null;
        if (!is_string($path) || !in_array($config['driver'] ?? null, ['single', 'daily'], true) || str_contains($path, '://')) {
            return [];
        }
        if ($config['driver'] === 'single') {
            return [$path];
        }
        $info = pathinfo($path);
        $date = new DateTimeImmutable('now', new DateTimeZone(config('app.timezone', 'UTC')));
        $prefix = ($info['dirname'] ?? '.').'/'.$info['filename'].'-';
        $extension = isset($info['extension']) ? '.'.$info['extension'] : '';
        return [$prefix.$date->format('Y-m-d').$extension, $prefix.$date->modify('-1 day')->format('Y-m-d').$extension];
    }

    private function readTail(string $path): array
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return [];
        }
        try {
            $size = fstat($handle)['size'] ?? 0;
            $offset = max(0, $size - 65536);
            fseek($handle, $offset);
            $raw = (string) fread($handle, 65536);
            if ($offset > 0) {
                $newline = strpos($raw, "\n");
                $raw = $newline === false ? '' : substr($raw, $newline + 1);
            }
            $entries = [];
            foreach (explode("\n", $raw) as $line) {
                if (!preg_match('/^\[([^\]]+)\]\s+[^\s]+\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):\s?(.*)$/', $line, $matches)) {
                    continue;
                }
                try {
                    $timestamp = (new DateTimeImmutable($matches[1], new DateTimeZone(config('app.timezone', 'UTC'))))->format(DATE_ATOM);
                } catch (Throwable $exception) {
                    continue;
                }
                $level = strtolower($matches[2]);
                $message = 'Entrée Laravel — contenu masqué';
                // Laravel's standard exception context contains a class before (code:...).
                // Do not send exception messages, SQL, request context, stack traces or payloads.
                if (preg_match('/\[object\]\s+\(([A-Za-z_][A-Za-z0-9_\\\\]*)\(code:/', $matches[3], $class)) {
                    $message = 'Exception : '.substr(str_replace('\\\\', '\\', $class[1]), 0, 160);
                }
                $entries[] = ['timestamp' => $timestamp, 'level' => $level, 'message' => $message];
            }
            return array_slice($entries, -50);
        } finally {
            fclose($handle);
        }
    }
}
