<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class NewProspectsPerLabelPerDay implements StatChartStatChart
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
        // List of labels ids
        // to show in he chart
        $labelsIds = is_array($this->statChart->labels) ? array_map(function($label) {
            return $label['id'];
        }, $this->statChart->labels) : [];

        // Select all prospects
        // associated to these labels
        $items = DB::table("prospects")
            ->join('prospect_label', 'prospects.id', '=', 'prospect_label.prospect_id')
            ->where('prospects.project_id', $this->statChart->project_id)
            ->when(!empty($labelsIds), function($query) use($labelsIds) {
                $query->whereIn('prospect_label.label_id', $labelsIds);
            })
            ->whereBetween('prospect_label.created_at', [$beginDate, $endDate])
            ->get(['prospect_label.label_id', 'prospect_label.created_at', 'prospect_label.deleted_at'])
            ;

        if (empty($labelsIds)) {
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
                    return 
                        // Created at the current date
                        substr($item->created_at, 0, 10) == $dateFormat;
                })->count();

                $result[$dateFormat] = $count;
            }

            // Data
            return [[
                'name' => "",
                'fillColor' => "#0077AA",
                'data' => $result
            ]];
        }

        // Count prospects
        // per label
        return collect($this->statChart->labels)->map(function($label) use($beginDate, $endDate, $items) {
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
                $count = $items->filter(function($item) use($label, $dateFormat) {
                    return 
                        // Associated to the current label
                        $item->label_id == $label['id'] && 
                        // Created at the current date
                        substr($item->created_at, 0, 10) == $dateFormat;
                })->count();

                $result[$dateFormat] = $count;
            }

            // Data
            return [
                'id' => $label['id'],
                'category_id' => $label['category_id'],
                'name' => $label['name'],
                'fillColor' => $label['fillColor'],
                'data' => $result
            ];
        });
    }
}