<?php

namespace App\Services\Monitoring;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use RuntimeException;
use Throwable;

/** Collects outside PHP-FPM; the dashboard only reads the published snapshot. */
class SystemCollector
{
    private array $options;
    private array $previous = [];
    private array $current = [];

    public function __construct(?array $options = null)
    {
        $this->options = $options ?? config('server-monitoring');
    }

    public function collectAndStore(): array
    {
        $directory = $this->options['directory'];
        if (!is_dir($directory) && !@mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le répertoire du monitoring.');
        }
        $state = @file_get_contents($directory.'/.system-state.json');
        $this->previous = $state ? (json_decode($state, true) ?: []) : [];
        $snapshot = $this->collect();
        $this->publish($directory.'/.system-state.json', $this->current);
        $this->publish($directory.'/system.json', $snapshot);

        return $snapshot;
    }

    public function collect(): array
    {
        $now = microtime(true);
        $this->current = ['time' => $now, 'docker' => []];
        // A failed Docker request must not erase the last real counter sample.
        // These private baselines never become displayed metrics for a failed poll.
        foreach ($this->previous['docker'] ?? [] as $id => $baseline) {
            $sampledAt = $baseline['time'] ?? $this->previous['time'] ?? 0;
            if ($sampledAt <= $now && $now - $sampledAt <= 60) {
                $baseline['time'] = $sampledAt;
                $this->current['docker'][$id] = $baseline;
            }
        }
        $snapshot = [
            'collected_at' => gmdate('c'),
            'host' => $this->host(),
            'docker' => $this->docker(),
        ];
        $this->previous = $this->current;

        return $snapshot;
    }

    private function host(): array
    {
        $proc = rtrim($this->options['host_proc'] ?? '/host/proc', '/');
        $sys = rtrim($this->options['host_sys'] ?? '/host/sys', '/');
        $root = $this->options['host_root'] ?? '/host/root';
        if (!is_readable($proc.'/stat')) {
            // /proc inside Docker is not a reliable view of the host or its network.
            $cgroup = @file_get_contents('/proc/1/cgroup') ?: '';
            $container = file_exists('/.dockerenv') || file_exists('/run/.containerenv')
                || preg_match('/docker|kubepods|containerd|lxc/', $cgroup);
            if (PHP_OS_FAMILY !== 'Linux' || $container) {
                return ['available' => false, 'reason' => 'Collecteur hôte non configuré : monter les ressources Linux dans le service monitoring.'];
            }
            $proc = '/proc';
            $sys = '/sys';
            $root = '/';
        }

        $boot = trim(@file_get_contents($proc.'/sys/kernel/random/boot_id') ?: '');
        $this->current['boot_id'] = $boot;
        $elapsed = $this->elapsed($boot);
        $stat = @file_get_contents($proc.'/stat') ?: '';
        preg_match('/^cpu\s+(.+)$/m', $stat, $match);
        $cpu = isset($match[1]) ? array_map('floatval', preg_split('/\s+/', trim($match[1]))) : [];
        $total = array_sum(array_slice($cpu, 0, 8)); // Guest time is already included in user/nice.
        $idle = ($cpu[3] ?? 0) + ($cpu[4] ?? 0);
        $this->current['cpu'] = ['total' => $total, 'idle' => $idle];
        $old = $this->previous['cpu'] ?? [];
        $delta = $total - ($old['total'] ?? $total);
        $idleDelta = $idle - ($old['idle'] ?? $idle);
        $usage = $elapsed !== null && $delta > 0 && $idleDelta >= 0 && $idleDelta <= $delta
            ? round(100 * ($delta - $idleDelta) / $delta, 2) : null;
        $cores = preg_match_all('/^cpu\d+\s/m', $stat);
        $load = preg_split('/\s+/', trim(@file_get_contents($proc.'/loadavg') ?: ''));
        $uptime = preg_split('/\s+/', trim(@file_get_contents($proc.'/uptime') ?: ''));

        $memory = [];
        preg_match_all('/^(\w+):\s+(\d+)\s+kB/m', @file_get_contents($proc.'/meminfo') ?: '', $matches, PREG_SET_ORDER);
        foreach ($matches as $entry) {
            $memory[$entry[1]] = (int) $entry[2] * 1024;
        }
        $memoryTotal = $memory['MemTotal'] ?? null;
        $available = $memory['MemAvailable'] ?? null;
        $memoryUsed = $memoryTotal !== null && $available !== null ? max(0, $memoryTotal - $available) : null;
        $diskTotal = is_dir($root) ? @disk_total_space($root) : false;
        $diskAvailable = is_dir($root) ? @disk_free_space($root) : false;

        // PID 1 belongs to the host proc mount; /proc/net would use our own network namespace.
        $dev = @file_get_contents($proc.'/1/net/dev');
        $interfaces = [];
        foreach (preg_split('/\r?\n/', $dev ?: '') as $line) {
            if (!preg_match('/^\s*([^:]+):\s*(.+)$/', $line, $parts)) {
                continue;
            }
            $name = trim($parts[1]);
            if (!preg_match('/^[\w.:-]+$/', $name) || preg_match('/^(lo$|veth|docker|br-|virbr)/', $name)
                || is_link($sys.'/class/net/'.$name.'/master')) {
                continue;
            }
            $values = preg_split('/\s+/', trim($parts[2]));
            if (count($values) < 9) {
                continue;
            }
            $rx = (float) $values[0];
            $tx = (float) $values[8];
            $ifindex = trim(@file_get_contents($sys.'/class/net/'.$name.'/ifindex') ?: '');
            $this->current['network'][$name] = ['rx' => $rx, 'tx' => $tx, 'ifindex' => $ifindex];
            $oldNet = $this->previous['network'][$name] ?? null;
            $valid = $elapsed !== null && $oldNet && $ifindex === ($oldNet['ifindex'] ?? null)
                && $rx >= $oldNet['rx'] && $tx >= $oldNet['tx'];
            $speed = trim(@file_get_contents($sys.'/class/net/'.$name.'/speed') ?: '');
            $interfaces[] = [
                'name' => $name,
                'rx_bytes_per_second' => $valid ? round(($rx - $oldNet['rx']) / $elapsed, 2) : null,
                'tx_bytes_per_second' => $valid ? round(($tx - $oldNet['tx']) / $elapsed, 2) : null,
                'speed_mbps' => is_numeric($speed) && (float) $speed > 0 ? (float) $speed : null,
            ];
        }
        $networkReady = $dev !== false && count($interfaces) > 0
            && count(array_filter($interfaces, fn ($item) => $item['rx_bytes_per_second'] === null)) === 0;

        return [
            'available' => $stat !== '',
            'reason' => $stat !== '' ? null : 'Compteurs Linux hôte illisibles.',
            'cpu' => [
                'usage_percent' => $usage,
                'cores' => $cores ?: null,
                'load' => count($load) >= 3 ? array_map('floatval', array_slice($load, 0, 3)) : null,
                'uptime_seconds' => isset($uptime[0]) && is_numeric($uptime[0]) ? (float) $uptime[0] : null,
            ],
            'memory' => [
                'total_bytes' => $memoryTotal, 'used_bytes' => $memoryUsed, 'available_bytes' => $available,
                'usage_percent' => $memoryTotal && $memoryUsed !== null ? round(100 * $memoryUsed / $memoryTotal, 2) : null,
            ],
            'disk' => [
                'total_bytes' => $diskTotal !== false ? $diskTotal : null,
                'used_bytes' => $diskTotal !== false && $diskAvailable !== false ? max(0, $diskTotal - $diskAvailable) : null,
                'available_bytes' => $diskAvailable !== false ? $diskAvailable : null,
                'usage_percent' => $diskTotal && $diskAvailable !== false ? round(100 * ($diskTotal - $diskAvailable) / $diskTotal, 2) : null,
                'scope' => 'Système de fichiers racine de l’hôte',
            ],
            'network' => [
                'available' => $dev !== false,
                'interfaces' => $interfaces,
                'rx_bytes_per_second' => $networkReady ? array_sum(array_column($interfaces, 'rx_bytes_per_second')) : null,
                'tx_bytes_per_second' => $networkReady ? array_sum(array_column($interfaces, 'tx_bytes_per_second')) : null,
            ],
        ];
    }

    private function elapsed(string $boot): ?float
    {
        $elapsed = $this->current['time'] - ($this->previous['time'] ?? $this->current['time']);

        return $boot !== '' && $boot === ($this->previous['boot_id'] ?? null) && $elapsed > 0
            && $elapsed <= max(60, 4 * ($this->options['interval'] ?? 10)) ? $elapsed : null;
    }

    private function docker(): array
    {
        $socket = $this->options['docker_socket'] ?? '/var/run/docker.sock';
        if (!extension_loaded('curl') || !is_readable($socket)) {
            return ['available' => false, 'reason' => 'Socket Docker ou extension PHP cURL indisponible pour le collecteur.', 'containers' => []];
        }
        $started = microtime(true);
        $budget = max(0.2, min(10, (float) ($this->options['docker_budget_seconds'] ?? 3)));
        $timeout = max(0.1, min($budget, (float) ($this->options['docker_timeout'] ?? 2.0)));
        $client = $this->dockerClient($socket, $timeout);
        try {
            $query = ['all' => 1];
            $project = trim((string) ($this->options['docker_project'] ?? ''));
            if ($project !== '') {
                // Ask the daemon to filter before building an expensive host-wide inventory.
                $query['filters'] = json_encode(['label' => ['com.docker.compose.project='.$project]], JSON_THROW_ON_ERROR);
            }
            $response = $client->get('/containers/json', ['query' => $query, 'timeout' => $timeout]);
            $rows = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($rows)) {
                throw new RuntimeException('Réponse Docker invalide.');
            }
        } catch (Throwable $exception) {
            return ['available' => false, 'reason' => 'Le moteur Docker ne répond pas au collecteur.', 'containers' => []];
        }
        $rows = array_values(array_filter($rows, fn ($row) => !$this->databaseContainer($row)));
        // Historical stopped containers must not displace active services at the limit.
        usort($rows, fn ($left, $right) => (($right['State'] ?? '') === 'running') <=> (($left['State'] ?? '') === 'running'));
        $limit = max(1, min(200, (int) ($this->options['docker_max_containers'] ?? 30)));
        $truncated = count($rows) > $limit;
        $containers = [];
        foreach (array_slice($rows, 0, $limit) as $row) {
            $id = $row['Id'] ?? '';
            if (!preg_match('/^[a-f0-9]{12,64}$/', $id)) {
                continue;
            }
            $containers[$id] = [
                'id' => substr($id, 0, 12), 'name' => ltrim($row['Names'][0] ?? substr($id, 0, 12), '/'),
                'state' => $row['State'] ?? 'unknown', 'status' => $row['Status'] ?? '',
                'cpu_percent' => null, 'memory_bytes' => null, 'memory_limit_bytes' => null, 'memory_percent' => null,
            ];
        }
        $running = array_keys(array_filter($containers, fn ($row) => $row['state'] === 'running'));
        $partial = false;
        foreach (array_chunk($running, 8) as $ids) {
            $remaining = $budget - (microtime(true) - $started);
            if ($remaining <= 0.05) {
                $partial = true;
                break;
            }
            $promises = [];
            foreach ($ids as $id) {
                $promises[$id] = $client->getAsync('/containers/'.$id.'/stats', [
                    'query' => ['stream' => 'false', 'one-shot' => 'true'], 'timeout' => min($timeout, $remaining),
                ]);
            }
            foreach (Utils::settle($promises)->wait() as $id => $result) {
                try {
                    if ($result['state'] !== 'fulfilled') {
                        throw new RuntimeException('Statistiques inaccessibles.');
                    }
                    $stats = json_decode((string) $result['value']->getBody(), true, 512, JSON_THROW_ON_ERROR);
                    $containers[$id] = array_merge($containers[$id], $this->dockerStats($id, $stats));
                } catch (Throwable $exception) {
                    $partial = true;
                    $containers[$id]['reason'] = 'Statistiques Docker momentanément indisponibles.';
                }
            }
        }

        return [
            'available' => true,
            'reason' => $partial ? 'Certaines statistiques sont indisponibles (délai de collecte limité).' : null,
            'truncated' => $truncated, 'total_containers' => count($rows), 'containers' => array_values($containers),
        ];
    }

    private function databaseContainer(array $row): bool
    {
        $text = ($row['Image'] ?? '').' '.implode(' ', $row['Names'] ?? []);
        $configured = $this->options['docker_exclude_images'] ?? null;

        return ($configured && @preg_match($configured, $text) === 1)
            || (bool) preg_match('/(^|[\s\/_.:-])(mysql|mariadb|postgres(?:ql)?|mongo(?:db)?|mssql|sqlserver|oracle|percona|clickhouse|cassandra|cockroachdb|influxdb)([\s\/_.:-]|$)/i', $text);
    }

    protected function dockerClient(string $socket, float $timeout): Client
    {
        return new Client([
            'base_uri' => 'http://localhost', 'timeout' => $timeout, 'connect_timeout' => $timeout,
            // This is always a local Unix socket, never an HTTP proxy destination.
            'proxy' => '',
            'curl' => [CURLOPT_UNIX_SOCKET_PATH => $socket],
        ]);
    }

    private function dockerStats(string $id, array $stats): array
    {
        $cpu = $stats['cpu_stats'] ?? [];
        $total = $cpu['cpu_usage']['total_usage'] ?? null;
        $system = $cpu['system_cpu_usage'] ?? null;
        $old = $this->previous['docker'][$id] ?? [];
        // Reset the baseline after a container restart, a host reboot, or a long pause.
        $sameStart = ($stats['read'] ?? '') !== '' && ($stats['cpu_stats']['cpu_usage']['total_usage'] ?? 0) >= ($old['total'] ?? PHP_INT_MAX);
        $now = microtime(true);
        $elapsed = $now - ($old['time'] ?? $this->previous['time'] ?? $now);
        $cpuPercent = null;
        $cores = $cpu['online_cpus'] ?? count($cpu['cpu_usage']['percpu_usage'] ?? []);
        if ($sameStart && $elapsed > 0 && $elapsed <= 60 && $total !== null && $system !== null && isset($old['system'])
            && $system > $old['system'] && $total >= $old['total'] && $cores > 0) {
            $cpuPercent = round(100 * ($total - $old['total']) / ($system - $old['system']) * $cores, 2);
        }
        if ($total !== null && $system !== null) {
            $this->current['docker'][$id] = ['total' => $total, 'system' => $system, 'time' => $now];
        }
        $memory = $stats['memory_stats'] ?? [];
        $usage = $memory['usage'] ?? null;
        $inactive = $memory['stats']['total_inactive_file'] ?? $memory['stats']['inactive_file'] ?? 0;
        $used = $usage !== null ? max(0, $usage - ($inactive < $usage ? $inactive : 0)) : null;
        $limit = $memory['limit'] ?? null;

        return [
            'cpu_percent' => $cpuPercent, 'memory_bytes' => $used, 'memory_limit_bytes' => $limit,
            'memory_percent' => $limit && $used !== null ? round(100 * $used / $limit, 2) : null,
        ];
    }

    private function publish(string $path, array $data): void
    {
        $temporary = tempnam(dirname($path), '.monitoring-');
        if ($temporary === false) {
            throw new RuntimeException('Impossible de préparer la publication du monitoring.');
        }
        try {
            if (file_put_contents($temporary, json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)) === false) {
                throw new RuntimeException('Écriture du monitoring impossible.');
            }
            @chmod($temporary, 0640);
            if (!rename($temporary, $path)) {
                throw new RuntimeException('Publication du monitoring impossible.');
            }
        } finally {
            if (file_exists($temporary)) {
                @unlink($temporary);
            }
        }
    }
}
