<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class ProspectsCount implements StatValue
{
    public function key()
    {
        return "stat.prospect.count";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('prospects')
                ->where('prospects.project_id', $projectId)
                ->whereNull('prospects.deleted_at')
                ->count();
            });
    }

    public function unity()
    {
        return "";
    }
}