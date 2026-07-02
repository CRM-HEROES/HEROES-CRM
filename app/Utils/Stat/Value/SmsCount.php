<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class SmsCount implements StatValue
{
    public function key()
    {
        return "stat.sms.count";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('sms')
                ->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
                ->where('prospects.project_id', $projectId)
                ->count();
            });
    }

    public function unity()
    {
        return "";
    }
}