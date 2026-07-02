<?php

namespace App\Console\Commands\Relevance;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UserEventRelevance extends UserProjectRelevance
{
    /**
     *
     */
    public function getData($from)
    {
        return DB::table('events')
            ->select(DB::raw('events.user_id as user_id'), DB::raw('calendars.project_id as project_id'), DB::raw('count(*) as relevance'))
            ->join('calendars', 'calendars.id', '=', 'events.calendar_id')
            ->groupBy('events.user_id', 'calendars.project_id')
            ->where('events.created_at', '>=', $from)
            ->whereNotNull('events.user_id')
            ->whereNotNull('calendars.project_id')
            ->get();
    }

    /**
     *
     */
    public function getField()
    {
        return "relevance_event";
    }

    /**
     *
     */
    public function getName()
    {
        return "User event relevance";
    }

}
