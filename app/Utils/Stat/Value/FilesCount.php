<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class FilesCount implements StatValue
{
    public function key()
    {
        return "stat.file.count";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('files')
                ->join('prospects', 'prospects.id', '=', 'files.prospect_id')
                ->where('prospects.project_id', $projectId)
                ->count();
            });
    }

    public function unity()
    {
        return "";
    }
}