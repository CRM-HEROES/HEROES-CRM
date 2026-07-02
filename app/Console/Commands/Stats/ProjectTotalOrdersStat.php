<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Models\OrderStatus;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectTotalOrdersStat implements StatInterface
{
    protected Project $project;
    protected ?OrderStatus $status;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Project $project, ?OrderStatus $status = null, $country = null)
    {
        $this->project = $project;
        $this->status = $status;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.order.total" . ($this->status ? ".status." . $this->status->id : "") . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        return DB::table('orders')
            ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
            ->where('prospects.project_id', $this->project->id)
            ->where('orders.created_at', '<=', $date . ' 23:59:59')
            ->when($this->status, function($query) {
                $query->where('orders.status_id', $this->status->id);
            })
            ->when($this->country, function($query) {
                $query->where('prospects.country', $this->country);
            })
            ->when($userId, function($query) use ($userId) {
                $query->where('orders.creator_id', $userId);
            })
            ->count();
    }
}
