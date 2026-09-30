<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckDuplicatedProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Project $project;
    protected $fields;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project, $fields)
    {
        $this->project = $project;
        $this->fields = $fields;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Reinitialize duplicated id

        DB::update("UPDATE prospects SET duplicate_id = NULL WHERE project_id = ?", [$this->project->id]);

        $fields = $this->project->fields()->whereIn('id', $this->fields)->get();

        if (empty($fields)) {
            return;
        }

        $params = [$this->project->id, $this->project->id];
        $query = "UPDATE prospects P1 INNER JOIN prospects P2 ON P1.project_id = ? AND P2.project_id = ? AND P1.id < P2.id";

    	foreach ($fields as $field) {
	        if ($field->meta) {
        		$query .= " AND (JSON_EXTRACT(P1.meta, '\$.$field->slug') IS NOT NULL AND JSON_EXTRACT(P1.meta, '\$.$field->slug') <> '' AND JSON_EXTRACT(P1.meta, '\$.$field->slug') = JSON_EXTRACT(P2.meta, '\$.$field->slug'))";
	        } else {
        		$query .= " AND (P1.$field->slug = P2.$field->slug)";
	        }
    	}

        $query .= " AND (";
        $query .= $fields
            ->map(function($field) {
                return "(P1.$field->slug IS NOT NULL AND P1.$field->slug <> '')";
            })
            ->join(" OR ");
        $query .= ")";

    	$query .= " SET P1.duplicate_id = P2.id";

        $i = DB::update($query, $params);
    }
}
