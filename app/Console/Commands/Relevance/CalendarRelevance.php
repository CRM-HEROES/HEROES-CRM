<?php

namespace App\Console\Commands\Relevance;

use App\Console\Commands\Relevance\Relevance;
use Illuminate\Support\Facades\DB;

class CalendarRelevance extends Relevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('events')
            ->select(DB::raw('calendar_id as item_id'), DB::raw('count(*) as relevance'))
            ->groupBy('calendar_id')
            ->where('created_at', '>=', $from)
            ->get();
    }

    /**
     *
     */
    public function getTable()
    {
        return "calendars";
    }

    /**
     *
     */
    public function getName()
    {
        return "Calendar relevance";
    }

}
