<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class TotalProspectsPerDay implements StatChartStatChart
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
        if (empty($this->statChart->labels)) {
            return [];
        }

        // Select all prospects
        $items = DB::table("prospects")
            ->where('prospects.project_id', $this->statChart->project_id)
            ->where('prospects.created_at', '<=', $endDate)
            ->get(['prospects.created_at', 'prospects.deleted_at'])
            ;

        // Count prospects
        // per label
        $result = [];

        for (
            // Begin date
            $date = new \Carbon\Carbon($beginDate), 
            // End date
            $end = new \Carbon\Carbon($endDate); 
            $date->lte($end); 
            $date = (new \Carbon\Carbon($date))->addDay()
        ) {
                // Count for the current date
            $count = $items->filter(function($item) use($date) {
                return 
                    // Created before the current date
                    (new \Carbon\Carbon($item->created_at))->lte($date) && (
                        // And not deleted
                        is_null($item->deleted_at) || 
                        // Or deleted after the current date
                        (new \Carbon\Carbon($item->deleted_at))->gte($date)
                    );
            })->count();

            $result[$date->format("Y-m-d")] = $count;
        }

        return [[
            'name' => "",
            'fillColor' => "#000000",
            'data' => $result
        ]];
    }
}