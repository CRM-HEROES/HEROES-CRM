<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class EventsCount implements StatValue
{

    public function key()
    {
        return "stat.event.count";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('events')
                ->join('prospects', 'prospects.id', '=', 'events.prospect_id')
                ->where('prospects.project_id', $projectId)
                ->count();
            });
    }

    public function unity()
    {
        return "";
    }
}