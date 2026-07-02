<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectTotalInteractionsDurationsStat implements StatInterface
{
    protected Project $project;
    protected $source;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, $source = null, $country = null)
    {
        $this->project = $project;
        $this->source = $source;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.interaction.duration.total" . ($this->source ? ".source." . $this->source : "") . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('interactions')
            ->join('prospects', 'prospects.id', '=', 'interactions.prospect_id')
            ->where('prospects.project_id', $this->project->id)
            ->where('interactions.created_at', '<=', $date . ' 23:59:59')
            ->whereNotNull('interactions.started_at')
            ->whereNotNull('interactions.ended_at')
            ->when($this->source, function($query) {
                $query->where('interactions.source', $this->source);
            })
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('interactions.creator_id', $userId);
            })
            ->sum(DB::raw('TIMESTAMPDIFF(SECOND, started_at, ended_at)'));
    }
}
