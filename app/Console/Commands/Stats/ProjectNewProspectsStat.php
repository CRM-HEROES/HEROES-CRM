<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectNewProspectsStat implements StatInterface
{
    protected Project $project;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, $country = null)
    {
        $this->project = $project;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.new" . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('prospects')
            ->where('prospects.project_id', $this->project->id)
            ->whereBetween('prospects.created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('prospects.creator_id', $userId);
            })
            ->count();
        /*$query = $this->metric->project
            ->prospects()
            ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59']);

        $prospectFilters = new ProspectFilters();

        if ($this->metric->filters) {
            try {
                foreach ($this->metric->filters as $k => $v) {
                    $prospectFilters->{$k}($query, $v);
                }
                
                return $query->count();
            } catch (\Exception $e) {
                return 0;
            }
        }*/
    }
}
