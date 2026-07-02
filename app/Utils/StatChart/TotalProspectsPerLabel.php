<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class TotalProspectsPerLabel implements StatChartStatChart
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

        // List of labels ids
        // to show in he chart
        $labelsIds = array_map(function($label) {
            return $label['id'];
        }, $this->statChart->labels);

        // Select all prospects
        // associated to these labels
        $items = DB::table("prospects")
            ->join('prospect_label', 'prospects.id', '=', 'prospect_label.prospect_id')
            ->where('prospects.project_id', $this->statChart->project_id)
            ->whereIn('prospect_label.label_id', $labelsIds)
            ->whereNull('prospect_label.deleted_at')
            ->where('prospect_label.created_at', '<=', $endDate)
            ->get(['prospect_label.label_id'])
            ;

        // Count prospects
        // per label
        return collect($this->statChart->labels)->map(function($label) use($items) {
            return [
                'id' => $label['id'],
                'category_id' => $label['category_id'],
                'name' => $label['name'],
                'fillColor' => $label['fillColor'],
                'data' => count($items->filter(function($item) use($label) {
                    return $item->label_id == $label['id'];
                }))
            ];
        });
    }
}