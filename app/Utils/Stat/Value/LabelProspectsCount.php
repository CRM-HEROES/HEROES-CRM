<?php

namespace App\Utils\Stat\Value;

use App\Utils\Stat\Value\StatValue;
use Illuminate\Support\Facades\DB;

class LabelProspectsCount implements StatValue
{
    protected $labelId;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($labelId)
    {
        $this->labelId = $labelId;
    }

    public function key()
    {
        return "stat.label.{$this->labelId}.prospect.count";
    }

    public function value($projectId)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($projectId) {
                return DB::table('prospects')
                ->join('prospect_label', 'prospects.id', '=', 'prospect_label.prospect_id')
                ->where('prospects.project_id', $projectId)
                ->where('prospect_label.label_id', $this->labelId)
                ->whereNull('prospects.deleted_at')
                ->count();
            });
    }

    public function unity()
    {
        return "";
    }
}