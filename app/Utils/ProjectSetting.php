<?php

namespace App\Utils;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

class ProjectSetting extends Facade
{
    
    /**
     * Log table
     */
    protected const TABLE = "project_settings";

    /**
     * Accessor
     */
    protected static function getFacadeAccessor()
    {
        return 'project_setting';
    }

    /**
     * Add setting
     */
    public static function set(Project $project, $setting, $value)
    {
        DB::table(ProjectSetting::TABLE)
            ->updateOrInsert([
                'project_id' => $project->id,
                'key' => $setting,
            ], [
                'value' => json_encode($value),
                'creator_id' => auth()->id(),
            ]);
    }

    /**
     * Get setting
     */
    public static function get(Project $project, $setting, $defaultValue = null)
    {
        if (is_array($setting)) {
            /*$settings = DB::table(ProjectSetting::TABLE)
                ->where('project_id', $project->id)
                ->whereIn('key', $setting)
                ->first();

            array_map(function($s) {
                $value = $settings->where()
            }, $setting);*/
        } else {
            $setting = DB::table(ProjectSetting::TABLE)
                ->where([
                    'project_id' => $project->id,
                    'key' => $setting,
                ])
                ->first();

            return $setting ? json_decode($setting->value) : $defaultValue;
        }
    }

    /**
     * Check setting
     */
    public static function check(Project $project, $setting)
    {
        return ProjectSetting::get($project, $setting) != null;
    }

    /**
     * Remove setting
     */
    public static function delete(Project $project, $setting)
    {
        return DB::table(ProjectSetting::TABLE)
            ->where([
                'project_id' => $project->id,
                'key' => $setting,
            ])
            ->delete();
    }

    /**
     * Log setting
     */
    public static function log(Project $project, $setting, $type, $log)
    {
        $setting = ProjectSetting::get($project, $setting);

        $log = $setting && is_array($setting->log) ? $setting->log : [];
        $log[] = [
            'type' => $type,
            'date' => \Carbon\Carbon::now(),
            'log' => $log,
        ];

        DB::table(ProjectSetting::TABLE)
            ->updateOrInsert([
                'project_id' => $project->id,
                'key' => $setting,
            ], [
                'log' => $log
            ]);
    }

    /**
     * Get log
     */
    public static function getlog(Project $project, $setting)
    {
        $setting = DB::table(ProjectSetting::TABLE)
            ->where([
                'project_id' => $project->id,
                'key' => $setting,
            ])
            ->first();

        return $setting ? $setting->log : null;
    }

    /**
     * Log clear
     */
    public static function clearlog(Project $project, $setting)
    {
        DB::table(ProjectSetting::TABLE)
            ->updateOrInsert([
                'project_id' => $project->id,
                'key' => $setting,
            ], [
                'log' => []
            ]);
    }

}