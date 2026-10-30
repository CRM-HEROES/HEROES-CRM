<?php

namespace App\Workflows;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Timezone of a project: the one its users have in mind when they talk about
 * "working hours". Stored in the project settings (key "timezone", an IANA
 * name such as "Europe/Paris"); the application timezone when not set.
 */
class ProjectTimezone
{
    public static function for(int $projectId): string
    {
        return Cache::remember("workflows:timezone:$projectId", 600, function () use ($projectId) {
            $value = DB::table('project_settings')
                ->where('project_id', $projectId)
                ->where('key', 'timezone')
                ->value('value');

            $timezone = is_string($value) ? json_decode($value, true) : $value;

            return is_string($timezone) && self::valid($timezone)
                ? $timezone
                : config('app.timezone');
        });
    }

    public static function valid(string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers(), true);
    }
}
