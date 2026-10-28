<?php

namespace App\Jobs;

use App\Models\Field;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RefreshProspectFieldDuplicates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Field $field) {}

    public function handle(): void
    {
        $field = $this->field->fresh();
        if (!$field || $field->for !== 'prospect' || $field->meta || !in_array($field->slug, ['email', 'mobile_phone_number', 'phone_number'], true)) {
            return;
        }

        $slug = $field->slug;
        $projectId = $field->project_id;
        $fieldJson = DB::getPdo()->quote($slug);

        // Rebuild only this field's marks, preserving duplicate marks from the other two fields.
        DB::statement("UPDATE prospects SET duplicate_group_id = CASE WHEN JSON_LENGTH(duplicate_fields) = 1 AND JSON_SEARCH(duplicate_fields, 'one', ?) IS NOT NULL THEN NULL ELSE duplicate_group_id END,
            duplicate_fields = CASE
            WHEN JSON_SEARCH(duplicate_fields, 'one', ?) IS NULL THEN duplicate_fields
            WHEN JSON_LENGTH(duplicate_fields) = 1 THEN NULL
            ELSE JSON_REMOVE(duplicate_fields, JSON_UNQUOTE(JSON_SEARCH(duplicate_fields, 'one', ?)))
            END
            WHERE project_id = ? AND duplicate_fields IS NOT NULL", [$slug, $slug, $slug, $projectId]);

        if (!$field->unique) {
            return;
        }

        // Grouping and tagging happen in SQL so memory use does not grow with project size.
        DB::statement("UPDATE prospects p
            JOIN (SELECT `$slug`, MIN(id) AS group_id FROM prospects
                WHERE project_id = ? AND `$slug` IS NOT NULL AND `$slug` <> ''
                GROUP BY `$slug` HAVING COUNT(*) > 1) d ON d.`$slug` = p.`$slug`
            SET p.duplicate_group_id = d.group_id,
                p.duplicate_fields = JSON_MERGE_PRESERVE(COALESCE(p.duplicate_fields, JSON_ARRAY()), JSON_ARRAY($fieldJson))
            WHERE p.project_id = ?", [$projectId, $projectId]);
    }
}
