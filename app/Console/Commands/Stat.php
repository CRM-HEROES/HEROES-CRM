<?php

namespace App\Console\Commands;

use App\Console\Commands\Stats\ProjectNewEventsStat;
use App\Console\Commands\Stats\ProjectNewFilesStat;
use App\Console\Commands\Stats\ProjectNewInteractionsStat;
use App\Console\Commands\Stats\ProjectNewInteractionsDurationsStat;
use App\Console\Commands\Stats\ProjectNewMessagesStat;
use App\Console\Commands\Stats\ProjectNewMetricStat;
use App\Console\Commands\Stats\ProjectNewOrdersStat;
use App\Console\Commands\Stats\ProjectNewOrdersValuesStat;
use App\Console\Commands\Stats\ProjectNewProspectsStat;
use App\Console\Commands\Stats\ProjectNewSmsStat;
use App\Console\Commands\Stats\ProjectTotalEventsStat;
use App\Console\Commands\Stats\ProjectTotalFilesStat;
use App\Console\Commands\Stats\ProjectTotalInteractionsStat;
use App\Console\Commands\Stats\ProjectTotalInteractionsDurationsStat;
use App\Console\Commands\Stats\ProjectTotalMessagesStat;
use App\Console\Commands\Stats\ProjectTotalMetricStat;
use App\Console\Commands\Stats\ProjectTotalOrdersStat;
use App\Console\Commands\Stats\ProjectTotalOrdersValuesStat;
use App\Console\Commands\Stats\ProjectTotalProspectsStat;
use App\Console\Commands\Stats\ProjectTotalSmsStat;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Stat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:stat {--projects=} {--keys=} {--date=} {--start=} {--end=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily stat';

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
        // date
        $dates = $this->dates();

        foreach ($dates as $date) {
            // Paginate projects
            $skip = 0;
            $take = 100;
    
            do {
                $projects = $this->projects($skip, $take);

                foreach($projects as $project) {
                    $this->createProjectStats($project, $date);
                }

                $skip += $take;
            } while ($projects->count() >= $take);
        }
    }

    /**
     * Stat date
     */
    protected function dates()
    {
        if ($this->option('date')) {
            return [$this->option('date')];
        }
        
        if ($this->option('start')) {
            $date = new \Carbon\Carbon($this->option('start'));
                
            if ($this->option('end')) {
                $end = new \Carbon\Carbon($this->option('end'));
            } else {
                $end = \Carbon\Carbon::yesterday();
            }
            
            $dates = [];
            while ($date->lte($end)) {
                $dates[] = $date->format("Y-m-d");
                $date = (new \Carbon\Carbon($date))->addDay();
            }

            return $dates;
        }
        
        return [\Carbon\Carbon::yesterday()->format("Y-m-d")];
    }

    /**
     * Stat projects
     */
    protected function projects($skip, $take) {
        return Project
            ::when($this->option('projects'), function($query) {
                $query->whereIn('id', explode(',', $this->option('projects')));
            })
            // to do
            // add filter
            // for projects that stat are activated
            ->skip($skip)
            ->take($take)
            ->get(['id']);
    }

    /**
     * Update or insert project stats for the $date
     */
    protected function createProjectStats(Project $project, $date) {
        $users = DB::table('user_project')
            ->where('project_id', $project->id)
            ->where('last_activity', '>=', $date . ' 00:00:00')
            ->get(['user_id'])
            ->map(function($user) {
                return $user->user_id;
            });

        foreach ($this->stats($project) as $stat) {
            $this->createProjectStat($project, $date, $stat, $users);
        }
    }

    /**
     * Update or insert stat for the $date
     */
    protected function createProjectStat(Project $project, $date, $stat, $users = []) {
        $value = $stat->value($date);

        // Do not store unchanged value
        // for DB optimization
        if ($value !== $this->previousStat($project, $date, $stat)) {
            DB::table('stats')
                ->updateOrInsert([
                    'project_id' => $project->id, 
                    'user_id' => null, 
                    'key' => $stat->key(), 
                    'date' => $date
                ], [
                    'value' => $value
                ]);
        }

        foreach ($users as $userId) {
            $value = $stat->value($date, $userId);

            DB::table('stats')
                ->updateOrInsert([
                    'project_id' => $project->id, 
                    'user_id' => $userId, 
                    'key' => $stat->key(), 
                    'date' => $date
                ], [
                    'value' => $value
                ]);    
        }
    }

    /**
     * Previous stat    
     */
    protected function previousStat(Project $project, $date, $stat) {
        // $yesterday = (new \Carbon\Carbon($date))->subDay();
        $value = DB::table('stats')
            ->where([
                'project_id' => $project->id, 
                'user_id' => null, 
                'key' => $stat->key(), 
            ])
            ->where('date', '<', $date)
            ->orderBy('date', 'desc')
            ->first(['value']);

        return $value ? $value->value : null;
    }

    /**
     * List of stats
     *
     */
    protected function stats(Project $project) {
        $keys = $this->option('keys') ? explode(',', $this->option('keys')): null;

        $countries = DB::table('prospects')
            ->distinct()
            ->select('country')
            ->where("project_id", $project->id)
            ->where('country', '!=', '')
            ->whereNotNull('country')
            ->get()
            ->map(function($country) {
                return $country->country;
            });

        $countries->prepend(null);
        $stats = [];

        foreach ($countries as $country) {
            $stats[] = new ProjectNewProspectsStat($project, $country);
            $stats[] = new ProjectNewSmsStat($project, null, $country);
            $stats[] = new ProjectNewInteractionsStat($project, null, $country);
            $stats[] = new ProjectNewInteractionsDurationsStat($project, null, $country);
            $stats[] = new ProjectNewEventsStat($project, null, $country);
            $stats[] = new ProjectNewOrdersStat($project, null, $country);
            $stats[] = new ProjectNewOrdersValuesStat($project, null, $country);
            $stats[] = new ProjectNewMessagesStat($project, null, $country);
            $stats[] = new ProjectNewFilesStat($project, null, $country);
            $stats[] = new ProjectTotalProspectsStat($project, $country);
            $stats[] = new ProjectTotalSmsStat($project, null, $country);
            $stats[] = new ProjectTotalInteractionsStat($project, null, $country);
            $stats[] = new ProjectTotalInteractionsDurationsStat($project, null, $country);
            $stats[] = new ProjectTotalEventsStat($project, null, $country);
            $stats[] = new ProjectTotalOrdersStat($project, null, $country);
            $stats[] = new ProjectTotalOrdersValuesStat($project, null, $country);
            $stats[] = new ProjectTotalMessagesStat($project, null, $country);
            $stats[] = new ProjectTotalFilesStat($project, null, $country);
        }
        
        $stats[] = new ProjectNewEventsStat($project, null, null);
        $stats[] = new ProjectTotalEventsStat($project, null, null);
        foreach ($project->calendars as $calendar) {
            $stats[] = new ProjectNewEventsStat($project, $calendar, null);
            $stats[] = new ProjectTotalEventsStat($project, $calendar, null);
        }
        
        $stats[] = new ProjectNewMessagesStat($project, null, null);
        $stats[] = new ProjectTotalMessagesStat($project, null, null);
        foreach ($project->threads as $thread) {
            $stats[] = new ProjectNewMessagesStat($project, $thread, null);
            $stats[] = new ProjectTotalMessagesStat($project, $thread, null);
        }
        
        $stats[] = new ProjectNewFilesStat($project, null, null);
        $stats[] = new ProjectTotalFilesStat($project, null, null);
        foreach ($project->folders as $folder) {
            $stats[] = new ProjectNewFilesStat($project, $folder, null);
            $stats[] = new ProjectTotalFilesStat($project, $folder, null);
        }
        
        $stats[] = new ProjectNewOrdersStat($project, null, null);
        $stats[] = new ProjectTotalOrdersStat($project, null, null);
        foreach ($project->orderStatuses as $orderStatus) {
            $stats[] = new ProjectNewOrdersStat($project, $orderStatus, null);
            $stats[] = new ProjectTotalOrdersStat($project, $orderStatus, null);
        }
        
        $stats[] = new ProjectNewSmsStat($project, null, null);
        $stats[] = new ProjectTotalSmsStat($project, null, null);
        foreach (['smsbox', 'ultramsg', 'telephone', 'whatsapp', 'ringover'] as $source) {
            $stats[] = new ProjectNewSmsStat($project, $source, null);
            $stats[] = new ProjectTotalSmsStat($project, $source, null);
        }
        
        $stats[] = new ProjectNewInteractionsStat($project, null, null);
        $stats[] = new ProjectNewInteractionsDurationsStat($project, null, null);
        $stats[] = new ProjectTotalInteractionsStat($project, null, null);
        $stats[] = new ProjectTotalInteractionsDurationsStat($project, null, null);
        foreach (['aircall', 'ringover', 'telephone'] as $source) {
            $stats[] = new ProjectNewInteractionsStat($project, $source, null);
            $stats[] = new ProjectNewInteractionsDurationsStat($project, $source, null);
            $stats[] = new ProjectTotalInteractionsStat($project, $source, null);
            $stats[] = new ProjectTotalInteractionsDurationsStat($project, $source, null);
        }
        
        /*foreach ($project->metrics as $metric) {
            if ($metric->for == 'prospect') {
                $stats[] = new ProjectTotalMetricStat($metric);
                $stats[] = new ProjectNewMetricStat($metric);
            }
        }*/

        return array_filter($stats, function($stat) use($keys) {
            return $keys ? in_array($stat->key(), $keys) : true;
        });
    }
}
