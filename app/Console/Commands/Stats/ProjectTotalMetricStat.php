<?php

namespace App\Console\Commands\Stats;

use App\Console\Commands\Stats\StatInterface;
use App\Filters\ProspectFilters;
use App\Models\Metric;
use App\Models\Project;
use App\Models\Prospect;

class ProjectTotalMetricStat implements StatInterface
{
    protected Metric $metric;
    protected $country;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Metric $metric, $country = null)
    {
        $this->metric = $metric;
        $this->country = $country;
    }

    /**
     * 
     */
    public function key()
    {
        return "prospect.total.metric." . $this->metric->id . ($this->country ? ".country." . $this->country : "");
    }
    
    /**
     * 
     */
    public function value($date, $userId = null)
    {
        $query = Prospect::query();
        $filter = new ProspectFilters();

        foreach ($this->metric->filters as $k => $v) {
            $filter->{$k}($query, $v);
        }

        return $query->count();
    }
}
