<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class TotalFilesPerDay implements StatChartStatChart
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

        // Select all files
        $items = DB::table("files")
            ->join('prospects', 'prospects.id', '=', 'files.prospect_id')
            ->where('prospects.project_id', $this->statChart->project_id)
            ->where('files.created_at', '<=', $endDate)
            ->get(['files.created_at'/*, 'files.deleted_at'*/])
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
                // Created before the current date
                return (new \Carbon\Carbon($item->created_at))->lte($date);
            })->count();

            $result[$date->format("Y-m-d")] = $count;
        }

        return [[
            'name' => "Devis",
            'fillColor' => "#000000",
            'data' => $result
        ]];
    }
}