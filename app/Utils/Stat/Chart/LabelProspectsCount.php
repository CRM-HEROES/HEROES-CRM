<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class LabelProspectsCount implements StatChart
{
    protected $labels;

    /**
     *
     * @return void
     */
    public function __construct($labels)
    {
        $this->labels = $labels;
    }

    public function key()
    {
        $keys = implode('-', array_map(function($label) {
            return $label['key'];
        }, $this->labels));

        return "stat.chart.label.{$keys}.prospect.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $prospects = DB::table('prospect_label')
                    ->select('prospect_label.label_id', DB::raw('count(*) as prospects_count'))
                    ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
                    ->whereIn('label_id',  array_map(function($label) {
                        return $label['key'];
                    }, $this->labels))
                    ->groupBy('prospect_label.label_id')
                    ->get();

                $labelValues = DB::table('labels')
                    ->whereIn('id', $prospects->map(function($v) {
                        return $v->label_id;
                    }))
                    ->get(['id', 'name', 'bgcolor']);

                $prospects = $prospects->map(function($prospect) use($labelValues) {
                    $label = $labelValues->where('id', $prospect->label_id)->first();

                    return array_merge((array) $label, [
                        'count' => $prospect->prospects_count
                    ]);
                });

                $labels = $prospects->map(function($prospect) {
                    return $prospect['name'];
                });

                $data = $prospects->map(function($prospect) {
                    return $prospect['count'];
                });

                $backgroundColor = $prospects->map(function($prospect) {
                    return $prospect['bgcolor'];
                });

                return [
                    'labels' => $labels,
                    'datasets' => [[
                        'data' => $data,
                        'backgroundColor' => $backgroundColor
                    ] ]
                ];
            });
    }
}