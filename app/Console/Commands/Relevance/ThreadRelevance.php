<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class ThreadRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('messages')
            ->select(DB::raw('thread_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('thread_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "threads";
    }

    /**
     *
     */
    public function getName()
    {
        return "Thread relevance";
    }

}
