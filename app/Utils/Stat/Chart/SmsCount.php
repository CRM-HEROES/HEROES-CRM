<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class SmsCount implements StatChart
{

    public function key()
    {
        return "stat.chart.sms.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $sources = [
                    'smsbox' => [
                        'name' => "SMSBOX",
                        'bgcolor' => "rgb(255, 99, 132)"
                    ],
                    'ultramsg' => [
                        'name' => "UltraMsg",
                        'bgcolor' => "rgb(54, 162, 235)"
                    ],
                    'mtarget' => [
                        'name' => "MTarget",
                        'bgcolor' => "rgb(0, 180, 160)"
                    ],
                    'telephone' => [
                        'name' => "Téléphone",
                        'bgcolor' => "rgb(255, 79, 55)"
                    ],
                    'whatsapp' => [
                        'name' => "Whatsapp",
                        'bgcolor' => "rgb(255, 205, 86)"
                    ],
                    'ringover' => [
                        'name' => "Ringover",
                        'bgcolor' => "rgb(0, 201, 76)"
                    ]
                ];

                $sms = DB::table('sms')
                    ->select('source', DB::raw('count(*) as sms_count'))
                    ->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
                    ->where('prospects.project_id', $project->id)
                    ->groupBy('source')
                    ->get();
                    
                $sms = $sms->map(function($sms) use($sources) {
                    if (isset($sources[$sms->source])) {
                        $sms->name = $sources[$sms->source]['name'];
                        $sms->bgcolor = $sources[$sms->source]['bgcolor'];
                    } else {
                        $sms->name = "";
                        $sms->bgcolor = "#FFFFFF";
                    }
                    
                    return $sms;
                });
    

                $labels = $sms->map(function($sms) {
                    return $sms->name;
                });

                $data = $sms->map(function($sms) {
                    return $sms->sms_count;
                });

                $backgroundColor = $sms->map(function($sms) {
                    return $sms->bgcolor;
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