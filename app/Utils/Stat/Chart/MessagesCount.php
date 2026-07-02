<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class MessagesCount implements StatChart
{

    public function key()
    {
        return "stat.chart.message.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $messages = DB::table('messages')
                    ->select('thread_id', DB::raw('count(*) as messages_count'))
                    ->join('threads', 'threads.id', '=', 'messages.thread_id')
                    ->where('threads.project_id', $project->id)
                    ->groupBy('thread_id')
                    ->get();

                $labelValues = DB::table('threads')
                    ->whereIn('id', $messages->map(function($v) {
                        return $v->thread_id;
                    }))
                    ->get(['id', 'name', 'bgcolor']);

                $messages = $messages->map(function($message) use($labelValues) {
                        $label = $labelValues->where('id', $message->thread_id)->first();
    
                        return array_merge((array) $label, [
                            'count' => $message->messages_count
                        ]);
                    });

                $labels = $messages->map(function($message) {
                    return $message['name'];
                });

                $data = $messages->map(function($message) {
                    return $message['count'];
                });

                $backgroundColor = $messages->map(function($message) {
                    return $message['bgcolor'];
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