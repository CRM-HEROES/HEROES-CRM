<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class HandleDuplicatedProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const MAPPING_FIELD_CLASSIC = 0;
    const MAPPING_FIELD_META = 1;
    const MAPPING_FIELD_CATEGORY = 2;

    protected Project $project;
    protected $action;
    protected $prospects;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project, $action, $prospects = [])
    {
        $this->project = $project;
        $this->action = $action;
        $this->prospects = $prospects;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $duplicates = $this->getDuplicates();

        switch ($this->action) {
            case "remove_duplicating":
                $this->removeDuplicating($duplicates);
                break;
            case "remove_duplicates":
                $this->removeDuplicates($duplicates);
                break;
        }
    }
    
    /**
     * Get list of duplicated prospects
     */
    protected function getDuplicates()
    {
        return $this->project
            ->prospects()
            ->with('duplicate:id')
            ->whereNotNull('duplicate_id')
            ->when(!empty($this->prospects), function($query) {
                $query->whereIn('prospects.id', $this->prospects);
            })
            ->get(['id', 'duplicate_id']);
    }
    
    /**
     * Remove duplicates
     */
    protected function removeDuplicates($duplicates)
    {
        $ids = $duplicates->map(function($prospect) {
            return $prospect->duplicate->id;
        })->toArray();

        $ids = array_unique($ids);
        $this->project->prospects()->whereIn('id', $ids)->delete();
    }
    
    /**
     * Remove duplicating
     */
    protected function removeDuplicating($duplicates)
    {
        $this->project->prospects()->whereIn('id', $duplicates->pluck('id'))->delete();
    }
}
