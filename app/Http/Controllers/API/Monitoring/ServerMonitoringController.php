<?php

namespace App\Http\Controllers\API\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\ServerMetric;
use App\Services\Monitoring\ServerMetricsCollector;
use Illuminate\Http\Request;

class ServerMonitoringController extends Controller
{
    /** period => [seconds, bucket size in seconds] */
    private const PERIODS = [
        '1h' => [3600, 60],
        '24h' => [86400, 300],
        '7d' => [604800, 1800],
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->check() && auth()->user()->is_super_admin, 404);

            return $next($request);
        });
    }

    /**
     * Live resources (cached a few seconds, never recomputed on each poll).
     */
    public function overview(ServerMetricsCollector $collector)
    {
        return response()->json($collector->live());
    }

    /**
     * Downsampled history and consumption peaks.
     */
    public function history(Request $request)
    {
        $period = $request->query('period', '1h');
        abort_unless(isset(self::PERIODS[$period]), 422);

        [$seconds, $bucket] = self::PERIODS[$period];
        $retentionDays = (int) config('monitoring.retention_days');
        $from = now()->subSeconds(min($seconds, $retentionDays * 86400));

        $rows = ServerMetric::where('recorded_at', '>=', $from)
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'cpu_percent', 'load_1', 'ram_percent', 'net_rx_bps', 'net_tx_bps']);

        $buckets = [];
        foreach ($rows as $r) {
            $key = intdiv($r->recorded_at->getTimestamp(), $bucket) * $bucket;
            $buckets[$key] ??= ['n' => 0, 'cpu' => 0, 'cpu_max' => 0, 'ram' => 0, 'rx' => 0, 'tx' => 0];
            $buckets[$key]['n']++;
            $buckets[$key]['cpu'] += $r->cpu_percent;
            $buckets[$key]['cpu_max'] = max($buckets[$key]['cpu_max'], $r->cpu_percent);
            $buckets[$key]['ram'] += $r->ram_percent;
            $buckets[$key]['rx'] += $r->net_rx_bps;
            $buckets[$key]['tx'] += $r->net_tx_bps;
        }

        $points = [];
        foreach ($buckets as $ts => $b) {
            $points[] = [
                't' => date('c', $ts),
                'cpu' => round($b['cpu'] / $b['n'], 1),
                'cpu_max' => round($b['cpu_max'], 1),
                'ram' => round($b['ram'] / $b['n'], 1),
                'rx_bps' => (int) ($b['rx'] / $b['n']),
                'tx_bps' => (int) ($b['tx'] / $b['n']),
            ];
        }

        return response()->json([
            'period' => $period,
            'retention_days' => $retentionDays,
            'points' => $points,
            'peaks' => $this->peaks($from),
            'thresholds' => [
                'cpu' => config('monitoring.peak_cpu'),
                'ram' => config('monitoring.peak_ram'),
            ],
        ]);
    }

    /**
     * Consecutive over-threshold samples are merged into one peak event,
     * keeping the worst sample and who was consuming at that time.
     */
    private function peaks($from): array
    {
        $cpuThr = config('monitoring.peak_cpu');
        $ramThr = config('monitoring.peak_ram');

        $rows = ServerMetric::where('recorded_at', '>=', $from)
            ->where(fn ($q) => $q->where('cpu_percent', '>=', $cpuThr)->orWhere('ram_percent', '>=', $ramThr))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'cpu_percent', 'ram_percent', 'details']);

        $events = [];
        $current = null;
        foreach ($rows as $r) {
            $ts = $r->recorded_at->getTimestamp();
            if ($current && $ts - $current['end'] <= 180) {
                $current['end'] = $ts;
            } else {
                if ($current) {
                    $events[] = $current;
                }
                $current = ['start' => $ts, 'end' => $ts, 'cpu' => 0, 'ram' => 0, 'details' => null, 'score' => -1];
            }
            $score = max($r->cpu_percent / $cpuThr, $r->ram_percent / $ramThr);
            if ($score > $current['score']) {
                $current['score'] = $score;
                $current['details'] = $r->details;
            }
            $current['cpu'] = max($current['cpu'], $r->cpu_percent);
            $current['ram'] = max($current['ram'], $r->ram_percent);
        }
        if ($current) {
            $events[] = $current;
        }

        $events = array_slice(array_reverse($events), 0, 50);

        return array_map(fn ($e) => [
            'started_at' => date('c', $e['start']),
            'ended_at' => date('c', $e['end']),
            'cpu_max' => round($e['cpu'], 1),
            'ram_max' => round($e['ram'], 1),
            'type' => array_values(array_filter([
                $e['cpu'] >= $cpuThr ? 'cpu' : null,
                $e['ram'] >= $ramThr ? 'ram' : null,
            ])),
            'details' => $e['details'],
        ], $events);
    }
}
