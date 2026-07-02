<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class NewSmsPerDay implements StatChartStatChart
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

        // Select all sms
        $items = DB::table("sms")
            ->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
            ->where('prospects.project_id', $this->statChart->project_id)
            ->whereBetween('sms.created_at', [$beginDate, $endDate])
            ->get(['sms.created_at'/*, 'sms.deleted_at'*/])
            ;

        // Count sms
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
            $dateFormat = $date->format("Y-m-d");

            // Count for the current date
            $count = $items->filter(function($item) use($dateFormat) {
                // Created at the current date
                return substr($item->created_at, 0, 10) == $dateFormat;
            })->count();

            $result[$dateFormat] = $count;
        }

        return [[
            'name' => "",
            'fillColor' => "#000000",
            'data' => $result
        ]];
    }
}