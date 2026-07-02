<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class TotalOrdersPricePerDay implements StatChartStatChart
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
        // Select all orders
        $items = DB::table("orders")
            ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id')
            ->where('prospects.project_id', $this->statChart->project_id)
            ->where('orders.created_at', '<=', $endDate)
            ->get(['orders.created_at', 'order_product.price', 'order_product.tax', 'order_product.including_tax', 'order_product.quantity'])
            ;

        // Count prospects
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
                // Count for the current date
            $count = $items->filter(function($item) use($date) {
                // Created before the current date
                return (new \Carbon\Carbon($item->created_at))->lte($date);
            })->reduce(function($carry, $product) {
                return $carry + ($product->including_tax ? $product->price : ($product->price * (1 + $product->tax))) * $product->quantity;
            }, 0);

            $result[$date->format("Y-m-d")] = $count;
        }

        return [[
            'name' => "Devis",
            'fillColor' => "#1e6ee5",
            'data' => $result
        ]];
    }
}