<?php

namespace App\Services\Monitoring;

class ServerMonitoring
{
    public function __construct(
        private TelemetryStore $telemetry,
        private LaravelMetrics $laravel,
        private RedisMetrics $redis,
    ) {
    }

    public function snapshot(): array
    {
        $enabled = config('server-monitoring.telemetry_enabled', true);
        if ($enabled) {
            $this->telemetry->initializeFailures();
        }
        $system = $this->system();
        $diagnostics = $this->diagnostics();
        if (!$enabled) {
            $diagnostics['laravel']['failed_jobs'] = ['available' => false, 'count' => null, 'entries' => [],
                'reason' => 'Le suivi des jobs échoués est désactivé.'];
        }
        $result = [
            'collected_at' => gmdate('c'),
            'system' => $system,
            'http' => $enabled ? $this->telemetry->httpSnapshot()
                : ['available' => false, 'reason' => 'La télémétrie HTTP est désactivée.'],
            'redis' => $diagnostics['redis'],
            'laravel' => $diagnostics['laravel'],
        ];
        $result['alerts'] = $this->alerts($result);

        return $result;
    }

    private function system(): array
    {
        $data = $this->readJson(config('server-monitoring.directory').'/system.json');
        $date = isset($data['collected_at']) ? strtotime($data['collected_at']) : false;
        if ($date && $date <= time() + 5 && time() - $date <= config('server-monitoring.stale_after', 40)
            && isset($data['host']['available'], $data['docker']['available'])) {
            return $data;
        }
        $reason = $date ? 'Relevé périmé : vérifier le collecteur de monitoring.'
            : 'Collecteur non démarré ou relevé inaccessible.';

        return ['collected_at' => $data['collected_at'] ?? null,
            'host' => ['available' => false, 'reason' => $reason],
            'docker' => ['available' => false, 'reason' => $reason, 'containers' => []]];
    }

    private function diagnostics(): array
    {
        $directory = config('server-monitoring.directory');
        if (!is_dir($directory)) {
            @mkdir($directory, 0770, true);
        }
        $path = $directory.'/diagnostics.json';
        $cached = $this->readJson($path);
        if ($cached && time() - ($cached['timestamp'] ?? 0) < config('server-monitoring.interval', 10)) {
            return $cached;
        }
        // One diagnostics collection across simultaneous administrators, independent of Redis/cache.
        $lock = is_dir($directory) ? @fopen($directory.'/diagnostics.lock', 'c') : false;
        if ($lock && @flock($lock, LOCK_EX | LOCK_NB)) {
            try {
                $result = ['timestamp' => time(), 'redis' => $this->redis->collect(), 'laravel' => $this->laravel->snapshot()];
                $temp = @tempnam($directory, 'diagnostics-');
                if ($temp) {
                    @chmod($temp, 0660);
                    @file_put_contents($temp, json_encode($result, JSON_INVALID_UTF8_SUBSTITUTE));
                    @rename($temp, $path);
                    if (is_file($temp)) {
                        @unlink($temp);
                    }
                }

                return $result;
            } finally {
                @flock($lock, LOCK_UN);
                @fclose($lock);
            }
        }
        if ($lock) {
            @fclose($lock);
        }
        if ($cached && time() - ($cached['timestamp'] ?? 0) <= config('server-monitoring.stale_after', 40)) {
            return $cached;
        }

        // On first use, TelemetryStore initializes the directory; if unwritable show the failure.
        return ['redis' => ['available' => false, 'reason' => 'Diagnostics en attente ou stockage inaccessible.'],
            'laravel' => ['logs' => ['available' => false, 'entries' => [], 'reason' => 'Diagnostics indisponibles.'],
                'failed_jobs' => ['available' => false, 'count' => null, 'entries' => [], 'reason' => 'Diagnostics indisponibles.']]];
    }

    private function readJson(string $path): ?array
    {
        if (!is_readable($path) || @filesize($path) > 2 * 1024 * 1024) {
            return null;
        }
        $decoded = json_decode(@file_get_contents($path) ?: '', true);

        return is_array($decoded) ? $decoded : null;
    }

    private function alerts(array $data): array
    {
        $alerts = [];
        foreach (['cpu' => 'CPU', 'memory' => 'RAM', 'disk' => 'Disque'] as $key => $label) {
            $value = $data['system']['host'][$key]['usage_percent'] ?? null;
            if ($value !== null && $value >= config('server-monitoring.thresholds.'.$key, 90)) {
                $alerts[] = ['level' => 'danger', 'key' => 'host_'.$key, 'message' => $label.' hôte : utilisation élevée ('.$value.' %).'];
            }
        }
        foreach (['host' => 'Mesures hôte', 'docker' => 'Docker'] as $key => $label) {
            if (!($data['system'][$key]['available'] ?? false)) {
                $alerts[] = ['level' => 'warning', 'key' => $key, 'message' => $label.' : '.($data['system'][$key]['reason'] ?? 'indisponible')];
            }
        }
        foreach (['redis' => $data['redis'], 'http' => $data['http'],
            'logs' => $data['laravel']['logs'], 'failed_jobs' => $data['laravel']['failed_jobs']] as $key => $metric) {
            if (!($metric['available'] ?? false)) {
                $alerts[] = ['level' => $key === 'redis' ? 'danger' : 'warning', 'key' => $key,
                    'message' => $metric['reason'] ?? ucfirst($key).' indisponible.'];
            }
        }
        if (($data['http']['errors_5xx'] ?? 0) > 0) {
            $alerts[] = ['level' => 'danger', 'key' => 'http_5xx', 'message' => $data['http']['errors_5xx'].' erreur(s) HTTP 5xx sur la fenêtre observée.'];
        }
        $recentLogErrors = array_filter($data['laravel']['logs']['entries'] ?? [], fn ($entry) =>
            in_array($entry['level'] ?? '', ['error', 'critical', 'alert', 'emergency'], true)
            && strtotime($entry['timestamp'] ?? '') >= time() - 300);
        if ($recentLogErrors) {
            $alerts[] = ['level' => 'danger', 'key' => 'laravel_errors',
                'message' => count($recentLogErrors).' erreur(s) Laravel dans les entrées consultables des 5 dernières minutes.'];
        }
        if (($data['laravel']['failed_jobs']['count'] ?? 0) > 0) {
            $alerts[] = ['level' => 'warning', 'key' => 'jobs', 'message' => $data['laravel']['failed_jobs']['count'].' job(s) échoué(s) observé(s) sur les dernières 24 h.'];
        }
        foreach ($data['system']['docker']['containers'] ?? [] as $container) {
            $expected = in_array($container['name'] ?? '', config('server-monitoring.expected_containers', []), true);
            if (in_array($container['state'] ?? '', ['restarting', 'dead'])
                || str_contains($container['status'] ?? '', 'unhealthy')
                || ($expected && ($container['state'] ?? '') !== 'running')) {
                $alerts[] = ['level' => 'danger', 'key' => 'container_'.($container['id'] ?? ''),
                    'message' => 'Conteneur '.$container['name'].' : '.$container['status']];
            }
        }
        if (($data['system']['docker']['available'] ?? false) && !($data['system']['docker']['truncated'] ?? false)) {
            $names = array_column($data['system']['docker']['containers'] ?? [], 'name');
            foreach (config('server-monitoring.expected_containers', []) as $name) {
                if (!in_array($name, $names, true)) {
                    $alerts[] = ['level' => 'danger', 'key' => 'missing_container_'.$name,
                        'message' => 'Service attendu absent de Docker : '.$name.'.'];
                }
            }
        }

        return $alerts;
    }
}
