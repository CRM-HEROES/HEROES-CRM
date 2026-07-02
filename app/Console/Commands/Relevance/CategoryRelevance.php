<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class CategoryRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('prospect_label')
            ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
            ->select(DB::raw('labels.category_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('labels.category_id')
            ->where('prospect_label.created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "categories";
    }

    /**
     *
     */
    public function getName()
    {
        return "Category relevance";
    }

}
