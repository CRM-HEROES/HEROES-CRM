<?php

namespace App\Console\Commands\Relevance;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UserOrderActionRelevance extends UserProjectRelevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('order_action')
            ->select(DB::raw('order_action.user_id as user_id'), DB::raw('order_actions.project_id as project_id'), DB::raw('count(*) as relevance'))
            ->join('order_actions', 'order_actions.id', '=', 'order_action.action_id')
            ->groupBy('order_action.user_id', 'order_actions.project_id')
            ->where('order_action.created_at', '>=', $from)
            ->whereNotNull('order_action.user_id')
            ->whereNotNull('order_actions.project_id')
            ->get();
    }

    /**
     *
     */
    public function getField()
    {
        return "relevance_order_action";
    }

    /**
     *
     */
    public function getName()
    {
        return "User order action relevance";
    }

}
