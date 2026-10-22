<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-tracker {--date=} {--from=} {--to=} {--dry-run : Compute without writing tracker_stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Persists each user\'s daily tracks on the app';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fromDate = Carbon::today();
        $toDate   = Carbon::today();

        if ($this->option('date')) {
            $fromDate = new Carbon($this->option('date'));
            $toDate   = new Carbon($this->option('date'));
        } else {
            if ($this->option('from')) {
                $fromDate = new Carbon($this->option('from'));
            }

            if ($this->option('to')) {
                $toDate = new Carbon($this->option('to'));
            }
        }

        list($first_date, $last_date) = $this->getFirstLastDates($fromDate, $toDate);

        while ($first_date->lte($toDate)) {
            $rows = $this->computeDay($first_date, $last_date);

            if ($this->option('dry-run')) {
                $this->info('[dry-run] ' . count($rows) . ' tracker_stats rows computed for ' . $first_date->format('d-m-Y'));
            } else {
                $this->storeDay($first_date, $rows);
                $this->info('User daily tracks stored for ' . $first_date->format('d-m-Y'));
            }

            list($first_date, $last_date) = $this->getNextDates($first_date, $last_date);
        }
    }

    /**
     * Compute the daily tracks of every user with a single aggregated query
     * (instead of loading each user's logs and filtering them in PHP).
     *
     * @return array<int, array{user_id:int, project_id:?int, hits:int, hits_per_time:array<int,int>}>
     */
    public function computeDay(Carbon $first_date, Carbon $last_date): array
    {
        // Hits per user, per project slug (null when the route is not a project route)
        // and per 15 minutes slot (0..95) of the day.
        $logs = DB::table('tracker_logs as l')
            ->join('tracker_route_paths as rp', 'rp.id', '=', 'l.route_path_id')
            ->whereNotNull('l.user_id')
            ->whereBetween('l.created_at', [$first_date, $last_date])
            ->groupBy('l.user_id', 'project_slug', 'slot')
            ->selectRaw(
                "l.user_id, " .
                "CASE WHEN rp.path LIKE 'api/project/%' " .
                "THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(rp.path, '/', 3), '/', -1) AS BINARY) END AS project_slug, " .
                "FLOOR(TIME_TO_SEC(TIME(l.created_at)) / 900) AS slot, " .
                "COUNT(*) AS hits"
            )
            ->get();

        if ($logs->isEmpty()) {
            return [];
        }

        // Projects of the users: only the projects a user belongs to are tracked
        $projects = [];
        DB::table('user_project as up')
            ->join('projects as p', 'p.id', '=', 'up.project_id')
            ->whereNull('p.deleted_at')
            ->whereIn('up.user_id', $logs->pluck('user_id')->unique()->all())
            ->get(['up.user_id', 'p.id', 'p.slug'])
            ->each(function ($project) use (&$projects) {
                $projects[$project->user_id][$project->slug] = $project->id;
            });

        $totals = [];
        $perProject = [];

        foreach ($logs as $log) {
            $slot = min(95, max(0, (int) $log->slot));
            $totals[$log->user_id][$slot] = ($totals[$log->user_id][$slot] ?? 0) + $log->hits;

            $projectId = $log->project_slug !== null ? ($projects[$log->user_id][$log->project_slug] ?? null) : null;
            if ($projectId !== null) {
                $perProject[$log->user_id][$projectId][$slot] = ($perProject[$log->user_id][$projectId][$slot] ?? 0) + $log->hits;
            }
        }

        $rows = [];
        foreach ($totals as $userId => $slots) {
            $rows[] = $this->makeRow($userId, null, $slots);

            foreach ($perProject[$userId] ?? [] as $projectId => $projectSlots) {
                $rows[] = $this->makeRow($userId, $projectId, $projectSlots);
            }
        }

        return $rows;
    }

    protected function makeRow($userId, $projectId, array $slots): array
    {
        $connections = [];

        for ($slot = 0; $slot < 96; $slot++) {
            $connections[] = (int) ($slots[$slot] ?? 0);
        }

        return [
            'user_id' => (int) $userId,
            'project_id' => $projectId !== null ? (int) $projectId : null,
            'hits' => array_sum($connections),
            'hits_per_time' => $connections,
        ];
    }

    /**
     * Insert the new rows by batch and update only the rows that changed.
     */
    protected function storeDay(Carbon $first_date, array $rows): void
    {
        $date = $first_date->format('Y-m-d');

        $existing = [];
        DB::table('tracker_stats')
            ->where('date', $date)
            ->get(['user_id', 'project_id', 'hits', 'hits_per_time'])
            ->each(function ($stat) use (&$existing) {
                $existing[$stat->user_id . ':' . ($stat->project_id ?? 'null')] = $stat;
            });

        $inserts = [];

        foreach ($rows as $row) {
            $json = json_encode($row['hits_per_time']);
            $key = $row['user_id'] . ':' . ($row['project_id'] ?? 'null');

            if (isset($existing[$key])) {
                if ((int) $existing[$key]->hits !== $row['hits'] || $existing[$key]->hits_per_time !== $json) {
                    DB::table('tracker_stats')
                        ->where('date', $date)
                        ->where('user_id', $row['user_id'])
                        ->where('project_id', $row['project_id'])
                        ->update([
                            'hits' => $row['hits'],
                            'hits_per_time' => $json,
                            'created_at' => $first_date,
                            'updated_at' => $first_date,
                        ]);
                }

                continue;
            }

            $inserts[] = [
                'user_id' => $row['user_id'],
                'project_id' => $row['project_id'],
                'date' => $date,
                'hits' => $row['hits'],
                'hits_per_time' => $json,
                'created_at' => $first_date,
                'updated_at' => $first_date,
            ];
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('tracker_stats')->insert($chunk);
        }
    }

    protected function getFirstLastDates($fromDate, $toDate)
    {
        return [
            (new Carbon($fromDate))->startOfDay(),
            (new Carbon($fromDate))->endOfDay()
        ];
    }

    protected function getNextDates($first_date, $last_date)
    {
        return [
            (new Carbon($first_date))->addDays(1)->startOfDay(),
            (new Carbon($first_date))->addDays(1)->endOfDay()
        ];
    }

}
