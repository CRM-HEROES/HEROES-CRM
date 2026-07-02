<?php

namespace App\Utils\StatChart;


use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class NewCommissionsPerUserPerDay implements StatChartStatChart
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

        // Select all orders
        $items = DB::table("orders")
            ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id')
            ->join('commissions', 'commissions.product_id', '=', 'order_product.product_id')
            ->whereIn('commissions.user_id', $labelsIds)
            ->where('prospects.project_id', $this->statChart->project_id)
            ->whereBetween('orders.created_at', [$beginDate, $endDate])
            ->get(['orders.created_at', 'commissions.user_id', 'order_product.price', 'commissions.value', 'commissions.type', 'commissions.including_tax', 'order_product.quantity'])
            ;

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
                    // Created at the current date
                    return 
                        $item->user_id == $label['id'] && substr($item->created_at, 0, 10) == $dateFormat;
                })->reduce(function($carry, $commission) {
                    return $carry + (
                        $commission->type == 'percentage' ? (
                            $commission->including_tax ? 
                                $commission->price : 
                                ($commission->price * (1 + $commission->value))
                        ) : $commission->value) 
                        * $commission->quantity;
                }, 0);

                $result[$dateFormat] = $count;
            }

            return [
                'name' => $label['name'],
                'fillColor' => $label['fillColor'],
                'data' => $result
            ];
        });
    }
}