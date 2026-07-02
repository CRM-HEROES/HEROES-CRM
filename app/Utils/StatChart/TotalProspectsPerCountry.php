<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class TotalProspectsPerCountry implements StatChartStatChart
{
    private StatChart $statChart;

    public function __construct(StatChart $statChart)
    {
        $this->statChart = $statChart;
    }

    /**
     * Get stat chart data
     * 
     * @param  content
     */
    public function data($beginDate, $endDate)
    {
        $zone = "country";

        if (
            is_array($this->statChart->options) && 
            isset($this->statChart->options['zone']) && 
            in_array($this->statChart->options['zone'], ['country', 'state', 'county'])
        ) {
            $zone = $this->statChart->options['zone'];
        }

        // Select all prospects
        $items = DB::table("prospects")
            ->select(DB::raw('prospects.' . $zone . ' as place'), DB::raw('count(*) as nb'))
            ->where('prospects.project_id', $this->statChart->project_id)
            ->groupBy('prospects.' . $zone)
            ->whereNotNull('prospects.' . $zone)
            ->where('prospects.' . $zone, '!=', "")
            ->where('prospects.created_at', '<=', $endDate)
            ->get()
            ;

        // Count prospects
        // per country
        $result = [];

        foreach ($items as $item) {
            $result[$item->place] = $item->nb;
        }

        if (
            $this->statChart->options && 
            isset($this->statChart->options['countries']) && 
            is_array($this->statChart->options['countries'])
        ) {
            foreach ($this->statChart->options['countries'] as $key => $value) {
                if (isset($result[$key])) {
                    if (!isset($result[$value])) {
                        $result[$value] = $result[$key];
                    } else {
                        $result[$value] += $result[$key];
                    }

                    unset($result[$key]);
                }
            }
        }

        return $result;
    }
}