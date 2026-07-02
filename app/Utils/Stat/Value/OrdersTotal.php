<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class OrdersTotal implements StatValue
{
    public function key()
    {
        return "stat.order.total";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return round(DB::table('orders')
                ->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
                ->join('order_product', 'orders.id', '=', 'order_product.order_id')
                ->where('prospects.project_id', $projectId)
                ->sum(DB::raw('order_product.price * order_product.quantity')), 2);
            });
    }

    public function unity()
    {
        return "€";
    }
}