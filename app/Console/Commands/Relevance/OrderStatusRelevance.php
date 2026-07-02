<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class OrderStatusRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('orders')
            ->select(DB::raw('status_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('status_id')
            ->where('created_at', '>=', $from)
            ->whereNotNull('status_id')
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "order_status";
    }

    /**
     *
     */
    public function getName()
    {
        return "Order status relevance";
    }

}
