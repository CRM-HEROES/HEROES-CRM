<?php

namespace App\Services;

use App\Models\Label;
use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

class ProspectLabelHistory
{
    /** Preserve targeted bulk assignments before their pivot rows are deleted. */
    public function preserveAssignments(array $prospects, array $labels): void
    {
        $this->saveAssignments($prospects, $labels, true);
    }

    /** Record every newly applied bulk assignment, including repeated actions. */
    public function recordAssignments(array $prospects, array $labels): void
    {
        $this->saveAssignments($prospects, $labels, false);
    }

    private function saveAssignments(array $prospects, array $labels, bool $onlyMissing): void
    {
        if (!$prospects || !$labels) {
            return;
        }

        $morph = (new Prospect())->getMorphClass();
        $driver = DB::connection()->getDriverName();
        $categoryKey = in_array($driver, ['sqlite', 'pgsql'], true)
            ? "('category->' || labels.category_id)"
            : "CONCAT('category->', labels.category_id)";
        $assignments = DB::table('prospect_label')
            ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
            ->whereIn('prospect_label.prospect_id', $prospects)
            ->whereIn('prospect_label.label_id', $labels)
            ->whereNotNull('prospect_label.created_at');

        if ($onlyMissing) {
            $assignments->whereNotExists(function ($query) use ($morph, $categoryKey) {
                $query->selectRaw('1')->from('revisions')
                    ->where('revisions.revisionable_type', $morph)
                    ->whereColumn('revisions.revisionable_id', 'prospect_label.prospect_id')
                    ->whereColumn('revisions.created_at', 'prospect_label.created_at')
                    ->whereRaw('revisions.key = ' . $categoryKey)
                    ->where(function ($query) {
                        $query->whereColumn('revisions.user_id', 'prospect_label.creator_id')
                            ->orWhere(fn ($query) => $query
                                ->whereNull('revisions.user_id')->whereNull('prospect_label.creator_id'));
                    });
                $this->whereRecordedAssignment($query);
            });
        }

        $assignments = $assignments->distinct()
            ->get(['prospect_label.prospect_id', 'prospect_label.creator_id', 'prospect_label.created_at', 'labels.category_id', 'labels.name']);

        foreach ($assignments->chunk(100) as $chunk) {
            DB::table('revisions')->insert($chunk->map(fn ($assignment) => [
                'revisionable_type' => $morph,
                'revisionable_id' => $assignment->prospect_id,
                'key' => 'category->' . $assignment->category_id,
                'old_value' => null,
                'new_value' => $assignment->name,
                'user_id' => $assignment->creator_id,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->created_at,
            ])->all());
        }
    }

    /**
     * Preserve legacy assignments before their mutable pivot rows are changed.
     */
    public function preserve(Prospect $prospect, Label $label): void
    {
        $key = 'category->' . $label->category_id;
        $labels = $prospect->labels()->where('category_id', $label->category_id)
            ->withPivot('creator_id', 'created_at')->get();

        foreach ($labels as $assigned) {
            if (!$assigned->pivot->created_at) {
                continue;
            }

            $recorded = $prospect->revisionHistory()->where('key', $key)
                ->where('created_at', $assigned->pivot->created_at)
                ->where('user_id', $assigned->pivot->creator_id);
            $this->whereRecordedAssignment($recorded, $assigned);
            if ($recorded->exists()) {
                continue;
            }

            $prospect->revisionHistory()->insert([
                'revisionable_type' => $prospect->getMorphClass(),
                'revisionable_id' => $prospect->getKey(),
                'key' => $key,
                'old_value' => null,
                'new_value' => $assigned->name,
                'user_id' => $assigned->pivot->creator_id,
                'created_at' => $assigned->pivot->created_at,
                'updated_at' => $assigned->pivot->created_at,
            ]);
        }
    }

    /**
     * Match the addition of a complete label name, including names with commas.
     * Without a label model this is used in a query correlated with labels.
     */
    public function whereRecordedAssignment($query, ?Label $label = null): void
    {
        $name = $label ? '?' : 'labels.name';
        $old = "COALESCE(revisions.old_value, '')";
        $new = "COALESCE(revisions.new_value, '')";
        $driver = $query->getConnection()->getDriverName();
        $concat = static function (array $parts) use ($driver): string {
            return in_array($driver, ['sqlite', 'pgsql'], true)
                ? '(' . implode(' || ', $parts) . ')'
                : 'CONCAT(' . implode(', ', $parts) . ')';
        };
        $oldList = $concat(["', '", $old, "', '"]);
        $nameInList = $concat(["', '", $name, "', '"]);
        $atStart = $concat([$name, "', '", $old]);
        $atEnd = $concat([$old, "', '", $name]);

        // A snapshot of another change in the same second is not this assignment.
        // Require the full name to be absent before, and verify that removing it
        // from the after snapshot reconstructs the before snapshot exactly.
        $sql = "REPLACE($oldList, $nameInList, '') = $oldList AND ("
            . "$new = $name OR ($old <> '' AND $new <> $old AND ("
            . "$new = $atStart OR $new = $atEnd OR "
            . "REPLACE($new, $nameInList, ', ') = $old)))";
        $query->whereRaw('(' . $sql . ')', $label ? array_fill(0, substr_count($sql, '?'), $label->name) : []);
    }

    public function value(Prospect $prospect, Label $label): string
    {
        return $prospect->labels()->where('category_id', $label->category_id)
            ->orderBy('labels.id')->pluck('name')->implode(', ');
    }

    public function record(Prospect $prospect, Label $label, string $oldValue, string $newValue, ?string $createdAt = null): void
    {
        $now = $createdAt ?? now();
        $prospect->revisionHistory()->insert([
            'revisionable_type' => $prospect->getMorphClass(),
            'revisionable_id' => $prospect->getKey(),
            'key' => 'category->' . $label->category_id,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => auth()->id(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
