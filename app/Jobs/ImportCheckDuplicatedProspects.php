<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCheckDuplicatedProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Import $import;
    protected $fields;

    /**
     * Create a new job instance.
     */
    public function __construct(Import $import, $fields)
    {
        $this->import = $import;
        $this->fields = $fields;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Reinitialize duplicated id

        DB::update("UPDATE prospects SET duplicate_id = NULL WHERE project_id = ?", [$this->import->project_id]);

        $params = [$this->import->project_id, $this->import->id, $this->import->id];
        $query = "UPDATE prospects P1 INNER JOIN prospects P2 ON P1.project_id = ? AND P1.import_id <> ? AND P2.import_id = ?";

    	foreach ($this->import->project->fields()->whereIn('id', $this->fields)->get() as $field) {
	        if ($field->meta) {
        		$query .= " AND (JSON_EXTRACT(P1.meta, '\$.$field->slug') IS NOT NULL AND JSON_EXTRACT(P1.meta, '\$.$field->slug') <> '' AND JSON_EXTRACT(P1.meta, '\$.$field->slug') = JSON_EXTRACT(P2.meta, '\$.$field->slug'))";
	        } else {
        		$query .= " AND (P1.$field->slug IS NOT NULL AND P1.$field->slug <> '' AND P1.$field->slug = P2.$field->slug)";
	        }
    	}

    	$query .= " SET P1.duplicate_id = P2.id";

        // dd($query);

    	$i = DB::update($query, $params);
        
        // dd($query);
    }
}
