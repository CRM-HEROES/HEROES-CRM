<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class OrdersProductsCount implements StatChart
{

    public function key()
    {
        return "stat.chart.order.product.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $orders = DB::table('order_product')
                    ->select('product_id', DB::raw('count(*) as orders_count'))
                    ->join('order_products', 'order_products.id', '=', 'order_product.product_id')
                    ->where('order_products.project_id', $project->id)
                    ->groupBy('product_id')
                    ->get();

                $labelValues = DB::table('products')
                    ->whereIn('id', $orders->map(function($v) {
                        return $v->product_id;
                    }))
                    ->get(['id', 'name', 'bgcolor']);

                $orders = $orders->map(function($order) use($labelValues) {
                        $label = $labelValues->where('id', $order->product_id)->first();
    
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