<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class OrderStepRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('order_step')
            ->select(DB::raw('step_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('step_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "order_steps";
    }

    /**
     *
     */
    public function getName()
    {
        return "Order step relevance";
    }

}
