<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class MessagesCount implements StatValue
{
    public function key()
    {
        return "stat.message.count";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('messages')
                ->join('prospects', 'prospects.id', '=', 'messages.prospect_id')
                ->where('prospects.project_id', $projectId)
                ->count();
            });
    }

    public function unity()
    {
        return "";
    }
}