<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class GroupRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('prospect_group')
            ->select(DB::raw('group_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('group_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "groups";
    }

    /**
     *
     */
    public function getName()
    {
        return "Group relevance";
    }

}
