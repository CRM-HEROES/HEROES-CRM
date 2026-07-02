<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class OrderActionRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('order_action')
            ->select(DB::raw('action_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('action_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "order_actions";
    }

    /**
     *
     */
    public function getName()
    {
        return "Order action relevance";
    }

}
