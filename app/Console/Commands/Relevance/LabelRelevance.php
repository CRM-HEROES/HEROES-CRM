<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class LabelRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('prospect_label')
            ->select(DB::raw('label_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('label_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "labels";
    }

    /**
     *
     */
    public function getName()
    {
        return "Label relevance";
    }

}
