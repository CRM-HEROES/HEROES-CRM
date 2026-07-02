<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\Calendar;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectTotalEventsStat implements StatInterface
{
    protected Project $project;
    protected ?Calendar $calendar;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, ?Calendar $calendar = null, $country = null)
    {
        $this->project = $project;
        $this->calendar = $calendar;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.event.total" . ($this->calendar ? ".calendar." . $this->calendar->id : "") . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('events')
            ->join('prospects', 'prospects.id', '=', 'events.prospect_id')
            ->where('prospects.project_id', $this->project->id)
            ->where('events.started_at', '<=', $date . ' 23:59:59')
            ->when($this->calendar, function($query) {
                $query->where('events.calendar_id', $this->calendar->id);
            })
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('events.user_id', $userId);
            })
            ->count();
    }
}
