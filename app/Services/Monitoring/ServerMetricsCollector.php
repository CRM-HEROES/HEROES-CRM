<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Read-only collector of real server metrics (procfs + optional Docker socket).
 * Never executes shell commands.
 */
class ServerMetricsCollector
{
    private const PREV_KEY = 'monitoring:previous';
    private const LIVE_KEY = 'monitoring:live';

    private string $proc;

    public function __construct()
    {
        $this->proc = rtrim((string) config('monitoring.proc_path', '/proc'), '/');
    }

    public function isSupported(): bool
    {
        return is_readable($this->proc . '/stat') && is_readable($this->proc . '/meminfo');
    }

    /**
     * Snapshot for the UI: served from cache for a few seconds so polling can
     * never cause repeated collections.
     */
    public function live(): array
    {
        $ttl = max(2, (int) config('monitoring.live_cache_seconds'));

        return Cache::store('file')->remember(self::LIVE_KEY, $ttl, fn () => $this->collect());
    }

    /**
     * Collect a fresh sample. CPU / network / process rates are computed
     * against the previous collection (kept in cache).
     */
    public function collect(): array
    {
        if (!$this->isSupported()) {
            return [
                'available' => false,
                'error' => 'Les métriques système (procfs) ne sont pas disponibles sur cet environnement (' . PHP_OS_FAMILY . ', lecture de "' . $this->proc . '/stat" impossible).',
            ];
        }

        $store = Cache::store('file');
        $prev = $store->get(self::PREV_KEY);
        $now = microtime(true);
        $errors = [];

        // Too close to the previous collection: rates would be meaningless
        if ($prev && isset($prev['sample']) && ($now - $prev['t']) < 2) {
            return $prev['sample'];
        }

        $elapsed = $prev ? max(0.001, $now - $prev['t']) : null;
        $raw = [];

        $sample = [
            'available' => true,
            'collected_at' => now()->toIso8601String(),
            'errors' => [],
        ];

        // CPU
        try {
            [$cpuTotal, $cpuIdle, $cores] = $this->readCpu();
            $raw['cpu'] = [$cpuTotal, $cpuIdle];
            $percent = null;
            if ($prev && isset($prev['cpu'])) {
                $dTotal = $cpuTotal - $prev['cpu'][0];
                $dIdle = $cpuIdle - $prev['cpu'][1];
                if ($dTotal > 0) {
                    $percent = max(0, min(100, (1 - $dIdle / $dTotal) * 100));
                }
            }
            $load = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
            $sample['cpu'] = [
                'percent' => $percent === null ? null : round($percent, 1),
                'cores' => $cores,
                'load' => $load ? array_map(fn ($v) => round($v, 2), $load) : null,
            ];
        } catch (Throwable $e) {
            $errors[] = 'cpu';
            $sample['cpu'] = null;
        }

        // RAM
        try {
            $sample['ram'] = $this->readMemory();
        } catch (Throwable $e) {
            $errors[] = 'ram';
            $sample['ram'] = null;
        }

        // Disk
        try {
            $path = config('monitoring.disk_path');
            $total = @disk_total_space($path);
            $free = @disk_free_space($path);
            if (!$total || $free === false) {
                throw new \RuntimeException('disk');
            }
            $used = $total - $free;
            $sample['disk'] = [
                'total' => (int) $total,
                'used' => (int) $used,
                'free' => (int) $free,
                'percent' => round($used / $total * 100, 1),
            ];
        } catch (Throwable $e) {
            $errors[] = 'disk';
            $sample['disk'] = null;
        }

        // Network
        try {
            [$rx, $tx] = $this->readNetwork();
            $raw['net'] = [$rx, $tx];
            $sample['network'] = [
                'rx_bps' => $prev && isset($prev['net']) ? (int) max(0, ($rx - $prev['net'][0]) / $elapsed) : null,
                'tx_bps' => $prev && isset($prev['net']) ? (int) max(0, ($tx - $prev['net'][1]) / $elapsed) : null,
                'rx_total' => $rx,
                'tx_total' => $tx,
            ];
        } catch (Throwable $e) {
            $errors[] = 'network';
            $sample['network'] = null;
        }

        // Processes
        try {
            $ticks = $this->readProcesses();
            $raw['procs'] = array_map(fn ($p) => $p['ticks'], $ticks);
            $raw['total_ticks'] = $raw['cpu'][0] ?? null;
            $sample['processes'] = $this->rankProcesses(
                $ticks,
                $prev['procs'] ?? null,
                isset($raw['cpu'], $prev['cpu']) ? $raw['cpu'][0] - $prev['cpu'][0] : null,
                $sample['ram']['total'] ?? null
            );
        } catch (Throwable $e) {
            $errors[] = 'processes';
            $sample['processes'] = null;
        }

        // Docker containers
        $sample['containers'] = $this->readContainers();

        $sample['errors'] = $errors;

        $raw['t'] = $now;
        $raw['sample'] = $sample;
        $store->put(self::PREV_KEY, $raw, 3600);

        return $sample;
    }

    private function readCpu(): array
    {
        $lines = file($this->proc . '/stat', FILE_IGNORE_NEW_LINES);
        $total = 0;
        $idle = 0;
        $cores = 0;
        foreach ($lines as $line) {
            if (preg_match('/^cpu\d+\s/', $line)) {
                $cores++;
            }
            if (str_starts_with($line, 'cpu ')) {
                $f = array_map('intval', preg_split('/\s+/', trim(substr($line, 4))));
                // user nice system idle iowait irq softirq steal
                $idle = ($f[3] ?? 0) + ($f[4] ?? 0);
                $total = array_sum(array_slice($f, 0, 8));
            }
        }
        if (!$total) {
            throw new \RuntimeException('cpu');
        }

        return [$total, $idle, max(1, $cores)];
    }

    private function readMemory(): array
    {
        $info = [];
        foreach (file($this->proc . '/meminfo', FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                $info[$m[1]] = (int) $m[2] * 1024;
            }
        }
        $total = $info['MemTotal'] ?? 0;
        if (!$total) {
            throw new \RuntimeException('ram');
        }
        $available = $info['MemAvailable']
            ?? (($info['MemFree'] ?? 0) + ($info['Buffers'] ?? 0) + ($info['Cached'] ?? 0));
        $used = max(0, $total - $available);

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
            'percent' => round($used / $total * 100, 1),
        ];
    }

    private function readNetwork(): array
    {
        $rx = 0;
        $tx = 0;
        foreach (file($this->proc . '/net/dev', FILE_IGNORE_NEW_LINES) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$iface, $data] = explode(':', $line, 2);
            $iface = trim($iface);
            if ($iface === 'lo') {
                continue;
            }
            $f = preg_split('/\s+/', trim($data));
            $rx += (int) ($f[0] ?? 0);
            $tx += (int) ($f[8] ?? 0);
        }

        return [$rx, $tx];
    }

    /**
     * @return array<int, array{name:string, ticks:int, rss:int}>
     */
    private function readProcesses(): array
    {
        $out = [];
        foreach (@scandir($this->proc) ?: [] as $entry) {
            if (!ctype_digit($entry)) {
                continue;
            }
            $stat = @file_get_contents($this->proc . '/' . $entry . '/stat');
            if ($stat === false || ($end = strrpos($stat, ')')) === false) {
                continue;
            }
            $start = strpos($stat, '(');
            // Only the executable name is exposed, never the command line
            $name = substr($stat, $start + 1, $end - $start - 1);
            $f = explode(' ', trim(substr($stat, $end + 2)));
            $out[(int) $entry] = [
                'name' => mb_substr($name, 0, 40),
                'ticks' => (int) ($f[11] ?? 0) + (int) ($f[12] ?? 0),
                'rss' => (int) ($f[21] ?? 0) * 4096,
            ];
        }

        return $out;
    }

    private function rankProcesses(array $procs, ?array $prevTicks, ?int $totalDelta, ?int $ramTotal): array
    {
        $list = [];
        foreach ($procs as $pid => $p) {
            $cpu = null;
            if ($prevTicks !== null && $totalDelta > 0 && isset($prevTicks[$pid])) {
                $cpu = round(max(0, $p['ticks'] - $prevTicks[$pid]) / $totalDelta * 100, 1);
            }
            $list[] = [
                'pid' => $pid,
                'name' => $p['name'],
                'cpu' => $cpu,
                'ram_bytes' => $p['rss'],
                'ram_percent' => $ramTotal ? round($p['rss'] / $ramTotal * 100, 1) : null,
            ];
        }

        $n = (int) config('monitoring.top_processes');
        $byCpu = $list;
        usort($byCpu, fn ($a, $b) => ($b['cpu'] ?? -1) <=> ($a['cpu'] ?? -1));
        $byRam = $list;
        usort($byRam, fn ($a, $b) => $b['ram_bytes'] <=> $a['ram_bytes']);

        $merged = [];
        foreach (array_merge(array_slice($byCpu, 0, $n), array_slice($byRam, 0, $n)) as $p) {
            $merged[$p['pid']] = $p;
        }

        return array_values($merged);
    }

    /**
     * Containers through the Docker Engine socket (GET only).
     */
    private function readContainers(): array
    {
        $socket = config('monitoring.docker_socket');
        if (!$socket || !@file_exists($socket)) {
            return ['available' => false, 'error' => 'Socket Docker non monté : statistiques des conteneurs indisponibles.', 'items' => []];
        }
        if (!function_exists('curl_init')) {
            return ['available' => false, 'error' => 'Extension curl indisponible.', 'items' => []];
        }

        try {
            $list = $this->dockerGet($socket, '/containers/json');
            if (!is_array($list)) {
                throw new \RuntimeException('docker');
            }
            $list = array_slice($list, 0, (int) config('monitoring.top_containers'));

            // Stats requests run in parallel (each waits ~1s for its CPU delta)
            $mh = curl_multi_init();
            $handles = [];
            foreach ($list as $c) {
                $ch = curl_init('http://localhost/containers/' . $c['Id'] . '/stats?stream=false');
                curl_setopt_array($ch, [
                    CURLOPT_UNIX_SOCKET_PATH => $socket,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_HTTPGET => true,
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$c['Id']] = $ch;
            }
            do {
                $status = curl_multi_exec($mh, $running);
                if ($running) {
                    curl_multi_select($mh, 0.5);
                }
            } while ($running && $status === CURLM_OK);

            $items = [];
            foreach ($list as $c) {
                $ch = $handles[$c['Id']];
                $stats = json_decode((string) curl_multi_getcontent($ch), true);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                if (!is_array($stats)) {
                    continue;
                }

                $cpuDelta = ($stats['cpu_stats']['cpu_usage']['total_usage'] ?? 0)
                    - ($stats['precpu_stats']['cpu_usage']['total_usage'] ?? 0);
                $sysDelta = ($stats['cpu_stats']['system_cpu_usage'] ?? 0)
                    - ($stats['precpu_stats']['system_cpu_usage'] ?? 0);
                // % of the whole machine, same scale as host CPU and processes
                $cpu = ($sysDelta > 0 && $cpuDelta >= 0) ? round($cpuDelta / $sysDelta * 100, 1) : null;

                $mem = $stats['memory_stats'] ?? [];
                $cache = $mem['stats']['inactive_file'] ?? ($mem['stats']['cache'] ?? 0);
                $used = max(0, ($mem['usage'] ?? 0) - $cache);
                $limit = $mem['limit'] ?? 0;

                $items[] = [
                    'name' => ltrim($c['Names'][0] ?? substr($c['Id'], 0, 12), '/'),
                    'image' => $c['Image'] ?? null,
                    'state' => $c['State'] ?? null,
                    'cpu' => $cpu,
                    'ram_bytes' => $used,
                    'ram_limit' => $limit ?: null,
                    'ram_percent' => $limit ? round($used / $limit * 100, 1) : null,
                ];
            }
            curl_multi_close($mh);

            return ['available' => true, 'error' => null, 'items' => $items];
        } catch (Throwable $e) {
            return ['available' => false, 'error' => 'Impossible de lire les statistiques Docker.', 'items' => []];
        }
    }

    private function dockerGet(string $socket, string $path)
    {
        $ch = curl_init('http://localhost' . $path);
        curl_setopt_array($ch, [
            CURLOPT_UNIX_SOCKET_PATH => $socket,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPGET => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($body !== false && $code === 200) ? json_decode($body, true) : null;
    }

    /**
     * Compact "who was consuming" payload stored with each history row.
     */
    public static function compactDetails(array $sample): ?array
    {
        $details = [];

        if (!empty($sample['processes'])) {
            $byCpu = $sample['processes'];
            usort($byCpu, fn ($a, $b) => ($b['cpu'] ?? -1) <=> ($a['cpu'] ?? -1));
            $byRam = $sample['processes'];
            usort($byRam, fn ($a, $b) => $b['ram_bytes'] <=> $a['ram_bytes']);
            $procs = [];
            foreach (array_merge(array_slice($byCpu, 0, 3), array_slice($byRam, 0, 3)) as $p) {
                $procs[$p['pid']] = ['name' => $p['name'], 'cpu' => $p['cpu'], 'ram_percent' => $p['ram_percent']];
            }
            $details['processes'] = array_values($procs);
        }

        if (!empty($sample['containers']['items'])) {
            $items = $sample['containers']['items'];
            usort($items, fn ($a, $b) => ($b['cpu'] ?? -1) <=> ($a['cpu'] ?? -1));
            $details['containers'] = array_map(
                fn ($c) => ['name' => $c['name'], 'cpu' => $c['cpu'], 'ram_bytes' => $c['ram_bytes']],
                array_slice($items, 0, 3)
            );
        }

        return $details ?: null;
    }
}
