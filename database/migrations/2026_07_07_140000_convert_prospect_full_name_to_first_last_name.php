<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convert legacy prospect "full_name" field to separate
 * "last_name" (Nom) and "first_name" (Prénom) fields, and
 * update the "prospects-table" column settings accordingly.
 *
 * The prospects table already exposes first_name / last_name
 * columns; only the per-project `fields` rows and the saved
 * column settings still referenced the removed full_name slug.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Convert every prospect "full_name" field into last_name + first_name
        $fullNameFields = DB::table('fields')
            ->where('for', 'prospect')
            ->where('slug', 'full_name')
            ->get();

        foreach ($fullNameFields as $field) {
            // Reuse the existing row as "last_name" (Nom)
            DB::table('fields')
                ->where('id', $field->id)
                ->update([
                    'slug'       => 'last_name',
                    'name'       => __('prospect.fields.last_name'),
                    'searchable' => true,
                    'updated_at' => now(),
                ]);

            // Create the matching "first_name" (Prénom) field
            // only if the project does not already have one.
            $hasFirstName = DB::table('fields')
                ->where('project_id', $field->project_id)
                ->where('for', 'prospect')
                ->where('slug', 'first_name')
                ->exists();

            if (! $hasFirstName) {
                DB::table('fields')->insert([
                    'project_id'    => $field->project_id,
                    'slug'          => 'first_name',
                    'name'          => __('prospect.fields.first_name'),
                    'type'          => $field->type,
                    'for'           => 'prospect',
                    'searchable'    => true,
                    'meta'          => false,
                    'required'      => (bool) $field->required,
                    'default_value' => null,
                    'order'         => $field->order,
                    'creator_id'    => $field->creator_id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // 2. Rewrite the "prospects-table" column settings
        foreach (['user_settings', 'project_settings'] as $table) {
            $this->replaceFullNameInSettings($table);
        }
    }

    /**
     * Replace the {"key":"full_name"} column entry with
     * {"key":"last_name"} + {"key":"first_name"} inside the
     * prospects-table settings of the given table.
     */
    protected function replaceFullNameInSettings(string $table): void
    {
        $rows = DB::table($table)
            ->where('key', 'prospects-table')
            ->where('value', 'like', '%full_name%')
            ->get();

        foreach ($rows as $row) {
            $columns = json_decode($row->value, true);

            if (! is_array($columns)) {
                continue;
            }

            $rewritten = [];
            foreach ($columns as $column) {
                if (($column['key'] ?? null) === 'full_name') {
                    $rewritten[] = ['key' => 'last_name'];
                    $rewritten[] = ['key' => 'first_name'];
                } else {
                    $rewritten[] = $column;
                }
            }

            DB::table($table)
                ->where('id', $row->id)
                ->update(['value' => json_encode($rewritten)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non reversible : the original full_name field carried no data
        // (the prospects table never had a full_name column).
    }
};