<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time data migration: Kavkom (per-user) and Ringover (per-project)
 * credentials used to live in user_settings/project_settings. Both are now
 * read from the "lines" table instead (see KavkomController::getUserKavkomConfig
 * and the "Lignes" project-setting bloc), so any agent who already had a
 * working Kavkom softphone must get an equivalent Line here, or their calls
 * would silently stop working the moment this branch deploys.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('user_settings')
            ->where('key', 'kavkom')
            ->get()
            ->each(function ($setting) {
                $config = json_decode($setting->value, true);

                if (!is_array($config) || empty($config['api_token']) || empty($config['domain_uuid'])) {
                    return;
                }

                $alreadyMigrated = DB::table('lines')
                    ->where('operator', 'kavkom')
                    ->where('user_id', $setting->user_id)
                    ->exists();

                if ($alreadyMigrated) {
                    return;
                }

                $projectId = DB::table('user_project')
                    ->where('user_id', $setting->user_id)
                    ->value('project_id');

                if (!$projectId) {
                    return;
                }

                DB::table('lines')->insert([
                    'project_id' => $projectId,
                    'name' => 'Kavkom (migré)',
                    'operator' => 'kavkom',
                    'user_id' => $setting->user_id,
                    'config' => $setting->value,
                    'creator_id' => $setting->creator_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        DB::table('project_settings')
            ->where('key', 'ringover')
            ->get()
            ->each(function ($setting) {
                $config = json_decode($setting->value, true);

                if (!is_array($config) || empty($config['api_token'])) {
                    return;
                }

                $alreadyMigrated = DB::table('lines')
                    ->where('operator', 'ringover')
                    ->where('project_id', $setting->project_id)
                    ->exists();

                if ($alreadyMigrated) {
                    return;
                }

                DB::table('lines')->insert([
                    'project_id' => $setting->project_id,
                    'name' => 'Ringover (migré)',
                    'operator' => 'ringover',
                    'user_id' => null,
                    'config' => $setting->value,
                    'creator_id' => $setting->creator_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('lines')
            ->whereIn('name', ['Kavkom (migré)', 'Ringover (migré)'])
            ->delete();
    }
};
