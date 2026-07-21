<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class InteractionsCount implements StatChart
{

    public function key()
    {
        return "stat.chart.interaction.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $sources = [
                    'aircall' => [
                        'name' => "Aircall",
                        'bgcolor' => "rgb(255, 99, 132)"
                    ],
                    'ringover' => [
                        'name' => "Ringover",
                        'bgcolor' => "rgb(0, 201, 76)"
                    ],
                    'telephone' => [
                        'name' => "Téléphone",
                        'bgcolor' => "rgb(255, 79, 55)"
                    ],
                    'kavkom' => [
                        'name' => "Kavkom",
                        'bgcolor' => "rgb(142, 36, 170)"
                    ]
                ];

                $interactions = DB::table('interactions')
                    ->select('source', DB::raw('count(*) as interactions_count'))
                    ->join('prospects', 'prospects.id', '=', 'interactions.prospect_id')
                    ->where('prospects.project_id', $project->id)
                    ->groupBy('source')
                    ->get();
                    
                $interactions = $interactions->map(function($interaction) use($sources) {
                    if (isset($sources[$interaction->source])) {
                        $interaction->name = $sources[$interaction->source]['name'];
                        $interaction->bgcolor = $sources[$interaction->source]['bgcolor'];
                    } else {
                        $interaction->name = "";
                        $interaction->bgcolor = "#FFFFFF";
                    }
                    
                    return $interaction;
                });
    

                $labels = $interactions->map(function($interaction) {
                    return $interaction->name;
                });

                $data = $interactions->map(function($interaction) {
                    return $interaction->interactions_count;
                });

                $backgroundColor = $interactions->map(function($interaction) {
                    return $interaction->bgcolor;
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