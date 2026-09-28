<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add the "AI agents" column to the saved prospects-table settings.
 *
 * Column settings are seeded at project creation / user attachment
 * (see App\Observers\ProjectObserver and App\Listeners\ProjectUserAttachedListener),
 * so projects and users created before the AI agents column existed never
 * got it — which is why "Affected users" showed but "AI agents" did not.
 * This appends {"key":"ai-agents"} to every existing setting that lacks it.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['user_settings', 'project_settings'] as $table) {
            $this->addAiAgentsColumn($table);
        }
    }

    /**
     * Append the AI agents column to the prospects-table settings
     * of the given table when it is missing.
     */
    protected function addAiAgentsColumn(string $table): void
    {
        $rows = DB::table($table)
            ->where('key', 'prospects-table')
            ->get();

        foreach ($rows as $row) {
            $columns = json_decode($row->value, true);

            if (! is_array($columns)) {
                continue;
            }

            $alreadyPresent = collect($columns)->contains(function ($column) {
                return ($column['key'] ?? null) === 'ai-agents';
            });

            if ($alreadyPresent) {
                continue;
            }

            $columns[] = ['key' => 'ai-agents'];

            DB::table($table)
                ->where('id', $row->id)
                ->update(['value' => json_encode($columns)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non reversible : the column was simply missing from the settings.
    }
};
