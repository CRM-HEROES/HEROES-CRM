<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class InteractionsDurations implements StatValue
{
    public function key()
    {
        return "stat.interaction.duration";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('interactions')
                ->join('prospects', 'prospects.id', '=', 'interactions.prospect_id')
                ->where('prospects.project_id', $projectId)
                ->sum(DB::raw('TIMESTAMPDIFF(SECOND, interactions.started_at, interactions.ended_at)'));
            });
    }

    public function unity()
    {
        return "s";
    }
}