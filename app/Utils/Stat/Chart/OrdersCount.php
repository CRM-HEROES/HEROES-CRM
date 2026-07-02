<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class OrdersCount implements StatChart
{

    public function key()
    {
        return "stat.chart.order.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $orders = DB::table('orders')
                    ->select('status_id', DB::raw('count(*) as orders_count'))
                    ->join('order_status', 'order_status.id', '=', 'orders.status_id')
                    ->where('order_status.project_id', $project->id)
                    ->groupBy('status_id')
                    ->get();

                $labelValues = DB::table('order_status')
                    ->whereIn('id', $orders->map(function($v) {
                        return $v->status_id;
                    }))
                    ->get(['id', 'name', 'bgcolor']);

                $orders = $orders->map(function($order) use($labelValues) {
                        $label = $labelValues->where('id', $order->status_id)->first();
    
                        return array_merge((array) $label, [
                            'count' => $order->orders_count
                        ]);
                    });

                $labels = $orders->map(function($order) {
                    return $order['name'];
                });

                $data = $orders->map(function($order) {
                    return $order['count'];
                });

                $backgroundColor = $orders->map(function($order) {
                    return $order['bgcolor'];
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