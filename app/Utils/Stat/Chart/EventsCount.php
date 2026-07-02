<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class EventsCount implements StatChart
{

    public function key()
    {
        return "stat.chart.event.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $events = DB::table('events')
                    ->select('calendar_id', DB::raw('count(*) as events_count'))
                    ->join('calendars', 'calendars.id', '=', 'events.calendar_id')
                    ->where('calendars.project_id', $project->id)
                    ->groupBy('calendar_id')
                    ->get();

                $labelValues = DB::table('calendars')
                    ->whereIn('id', $events->map(function($v) {
                        return $v->calendar_id;
                    }))
                    ->get(['id', 'name', 'bgcolor']);

                $events = $events->map(function($event) use($labelValues) {
                        $label = $labelValues->where('id', $event->calendar_id)->first();
    
                        return array_merge((array) $label, [
                            'count' => $event->events_count
                        ]);
                    });

                $labels = $events->map(function($event) {
                    return $event['name'];
                });

                $data = $events->map(function($event) {
                    return $event['count'];
                });

                $backgroundColor = $events->map(function($event) {
                    return $event['bgcolor'];
                });
    
                return [
                    'labels' => $labels,
                    'datasets' => [[
                        'data' => $data,
                        'backgroundColor' => $backgroundColor
                    ]]
                ];
            });
    }
}