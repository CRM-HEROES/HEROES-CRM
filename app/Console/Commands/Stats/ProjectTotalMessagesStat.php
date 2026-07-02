<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\Project;
use App\Models\Thread;
use Illuminate\Support\Facades\DB;

class ProjectTotalMessagesStat implements StatInterface
{
    protected Project $project;
    protected ?Thread $thread;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, ?Thread $thread = null, $country = null)
    {
        $this->project = $project;
        $this->thread = $thread;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.message.total" . ($this->thread ? ".thread." . $this->thread->id : "") . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('messages')
            ->join('prospects', 'prospects.id', '=', 'messages.prospect_id')
            // ->join('threads', 'threads.id', '=', 'messages.thread_id')
            ->where('prospects.project_id', $this->project->id)
            // ->where('threads.private', 0)
            ->where('messages.created_at', '<=', $date . ' 23:59:59')
            ->when($this->thread, function($query) {
                $query->where('messages.thread_id', $this->thread->id);
            })
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('messages.creator_id', $userId);
            })
            ->count();
    }
}
