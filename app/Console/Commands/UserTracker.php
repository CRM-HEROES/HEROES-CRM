<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Utils\Tracker\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserTracker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-tracker {--date=} {--from=} {--to=}';

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
            $dates_interval = [$first_date, $last_date];
            
            $users = User::all();

            foreach ($users as $user) {
                $connections = [];
                $hits = 0;

                $dateConnections = Log::has('routePath')
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', $dates_interval)
                    ->get();

                if ($dateConnections->count() == 0) {
                    continue;
                }

                foreach (range(0, 23) as $hour) {
                    foreach ([0, 15, 30, 45] as $minutes) {
                        $timeConnections = $dateConnections
                            ->where('created_at', '>=', $first_date->copy()->addHours($hour)->addMinutes($minutes))
                            ->where('created_at', '<', $first_date->copy()->addHours($hour)->addMinutes($minutes + 15))
                            ->count();
                        $hits += $timeConnections;
                        $connections[] = $timeConnections;
                    }
                }

                DB::table('tracker_stats')->updateOrInsert([
                    'user_id' => $user->id,
                    'project_id' => null,
                    'date' => $first_date
                ], [
                    'hits' => $hits,
                    'hits_per_time' => json_encode($connections),
                    'created_at' => $first_date,
                    'updated_at' => $first_date,
                ]);

                foreach ($user->projects as $project) {
                    $projectUrl = 'api/project/' . $project->slug;
                    $connections = [];
                    $hits = 0;

                    $dateProjectConnections = $dateConnections->filter(function($log) use($projectUrl) {
                        return 
                            $log->routePath->path == $projectUrl ||
                            Str::startsWith($log->routePath->path, $projectUrl . '/');
                    });

                    if ($dateProjectConnections->count() == 0) {
                        continue;
                    }
    
                    foreach (range(0, 23) as $hour) {
                        foreach ([0, 15, 30, 45] as $minutes) {
                            $timeConnections = $dateProjectConnections
                                ->where('created_at', '>=', $first_date->copy()->addHours($hour)->addMinutes($minutes))
                                ->where('created_at', '<', $first_date->copy()->addHours($hour)->addMinutes($minutes + 15))
                                ->count();
                            $hits += $timeConnections;
                            $connections[] = $timeConnections;
                        }
                    }

                    DB::table('tracker_stats')->updateOrInsert([
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'date' => $first_date,
                    ], [
                        'hits' => $hits,
                        'hits_per_time' => json_encode($connections),
                        'created_at' => $first_date,
                        'updated_at' => $first_date,
                    ]);
                }
            }

            $this->info('User daily tracks stored for ' . $first_date->format('d-m-Y'));

            list($first_date, $last_date) = $this->getNextDates($first_date, $last_date);
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
