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
        // NOTE: this used to reset every prospect's duplicate_id/
        // duplicate_group_id for the whole project before recomputing.
        // That made two consecutive runs with different field selections
        // destructive: checking "Téléphone" after a previous "Email" run
        // wiped out the still-valid email-based duplicates instead of
        // leaving them alone. Matching below is purely additive — it only
        // ever sets duplicate_id/duplicate_group_id for prospects that
        // match under the fields being checked right now, never clears
        // matches found by a different combination (whether from an
        // earlier run of this job or the real-time checker in
        // ProspectDuplicateChecker).
        $fields = $this->project->fields()->whereIn('id', $this->fields)->get();

        if (empty($fields)) {
            return;
        }

        // Pairwise pointer (earlier -> later), used only by the
        // duplicate-management panel to list each matching pair once.
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

        DB::update($query, $params);

        $this->assignDuplicateGroups($fields);
    }

    /**
     * Flags every prospect sharing the exact same value(s) on the checked
     * fields with a shared duplicate_group_id (the lowest id in that set),
     * so the table can highlight every member of a cluster — not just the
     * one duplicate_id happens to point at — and give each cluster of
     * duplicates its own color. Equality is transitive, so "prospects
     * sharing the same value" is already the full cluster: no recursion
     * needed, just a MIN()/COUNT() partitioned by that value.
     */
    protected function assignDuplicateGroups($fields): void
    {
        $keyExpr = $fields
            ->map(function($field) {
                return $field->meta
                    ? "JSON_EXTRACT(meta, '\$.$field->slug')"
                    : $field->slug;
            })
            ->join(", '||', ");
        $keyExpr = "CONCAT_WS('||', $keyExpr)";

        $nonBlank = $fields
            ->map(function($field) {
                $expr = $field->meta ? "JSON_EXTRACT(meta, '\$.$field->slug')" : $field->slug;
                return "($expr IS NOT NULL AND $expr <> '')";
            })
            ->join(" OR ");

        // Checked fields matched together (AND) to form these groups, so
        // every matched prospect gets tagged with the whole set — merged
        // with whatever it was already tagged with, so a prospect caught
        // by both this run and an earlier/different field combination
        // keeps every cell it should highlight, not just the latest one.
        $fieldSlugsJson = 'JSON_ARRAY(' . $fields->map(fn($field) => DB::getPdo()->quote($field->slug))->join(',') . ')';

        $query = "UPDATE prospects P
            JOIN (
                SELECT id,
                    MIN(id) OVER (PARTITION BY $keyExpr) AS group_id,
                    COUNT(*) OVER (PARTITION BY $keyExpr) AS cnt
                FROM prospects
                WHERE project_id = ? AND ($nonBlank)
            ) G ON G.id = P.id
            SET P.duplicate_group_id = G.group_id,
                P.duplicate_fields = JSON_MERGE_PRESERVE(COALESCE(P.duplicate_fields, JSON_ARRAY()), $fieldSlugsJson)
            WHERE G.cnt > 1";

        DB::update($query, [$this->project->id]);
    }
}
