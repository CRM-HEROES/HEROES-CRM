<?php

namespace App\Jobs;

use App\Console\Commands\Stats\ProjectNewProspectsStat;
use App\Console\Commands\Stats\ProjectTotalProspectsStat;
use App\Models\Metric;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class StatMetricProspect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Metric $metric;

    /**
     * Create a new job instance.
     */
    public function __construct(Metric $metric)
    {
        $this->metric = $metric;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $date = new \Carbon\Carbon($this->metric);
        $end = \Carbon\Carbon::yesterday();

        $stats = [
            new ProjectNewProspectsStat($this->metric),
            new ProjectTotalProspectsStat($this->metric),
        ];
        
        while ($date->lte($end)) {
            foreach ($stats as $stat) {
                $this->createProjectStat(
                    $this->metric->project,
                    $date->format("Y-m-d"),
                    $stat
                );
            }
            
            $date = (new \Carbon\Carbon($date))->addDay();
        }
    }
    
    /**
     * Update or insert stat for the $date
     */
    protected function createProjectStat(Project $project, $date, $stat) {
        DB::table('stats')
            ->updateOrInsert([
                'project_id' => $project->id, 
                'user_id' => null, 
                'key' => $stat->key(), 
                'date' => $date
            ], [
                'value' => $stat->value($date)
            ]);
    }
}
