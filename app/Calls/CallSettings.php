<?php

namespace App\Calls;

use App\Models\Project;
use App\Utils\ProjectSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CallSettings
{
    public static function globalEnabled(): bool
    {
        if (Schema::hasTable('project_settings')) {
            $setting = DB::table('project_settings')
                ->whereNull('project_id')
                ->where('key', 'calls.global_enabled')
                ->first(['value']);

            if ($setting !== null) {
                return (bool) json_decode((string) $setting->value, true);
            }
        }

        return (bool) config('calls.enabled', false);
    }

    public static function forProject(Project $project): array
    {
        if (!Schema::hasTable('project_settings')) {
            return [];
        }

        $settings = ProjectSetting::get($project, 'calls', []);

        return is_object($settings) ? (array) $settings : (array) $settings;
    }

    public static function provider(Project $project): string
    {
        $provider = self::forProject($project)['provider'] ?? null;

        return is_string($provider) && trim($provider) !== ''
            ? $provider
            : (string) config('calls.provider', 'fake');
    }

    public static function projectEnabled(Project $project): bool
    {
        return (bool) DB::table('projects')
            ->where('id', $project->id)
            ->value('calls_enabled');
    }

    public static function setGlobalEnabled(bool $enabled): void
    {
        DB::table('project_settings')->updateOrInsert(
            ['project_id' => null, 'key' => 'calls.global_enabled'],
            [
                'value' => json_encode($enabled),
                'creator_id' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
