<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectTotalSmsStat implements StatInterface
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
        return "prospect.sms.total" . ($this->source ? ".source." . $this->source : "") . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('sms')
            ->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
            ->where('prospects.project_id', $this->project->id)
            ->where('sms.created_at', '<=', $date . ' 23:59:59')
            ->when($this->source, function($query) {
                $query->where('sms.source', $this->source);
            })
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('sms.creator_id', $userId);
            })
            ->count();
    }
}
