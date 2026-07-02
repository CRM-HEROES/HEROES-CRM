<?php

namespace App\Utils;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

class ProjectLog extends Facade
{
    
    public const INFO = "info";
    public const WARNING = "warning";
    public const ERROR = "error";
    public const DEBUG = "debug";

    /**
     * Log table
     */
    protected const TABLE = "project_logs";

    /**
     * Accessor
     */
    protected static function getFacadeAccessor()
    {
        return 'project_log';
    }

    /**
     * Add Log
     */
    protected static function add(Project $project, $type, $log)
    {
        DB::table(ProjectLog::TABLE)
            ->insert([
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'log' => $log,
                'date' => \Carbon\Carbon::now()->format('Y-m-d h:i:s'),
            ]);
    }

    /**
     * Info Log
     */
    public static function info(Project $project, $log)
    {
        ProjectLog::add($project, ProjectLog::INFO, $log);
    }

    /**
     * Warning Log
     */
    public static function warning(Project $project, $log)
    {
        ProjectLog::add($project, ProjectLog::WARNING, $log);
    }

    /**
     * Error Log
     */
    public static function error(Project $project, $log)
    {
        ProjectLog::add($project, ProjectLog::ERROR, $log);
    }

    /**
     * Debug Log
     */
    public static function debug(Project $project, $log)
    {
        ProjectLog::add($project, ProjectLog::DEBUG, $log);
    }
    
    /**
     * Get Log
     */
    public static function get(Project $project, $startDate = null, $endDate = null, $type = null)
    {
        return DB::table(ProjectLog::TABLE)
            ->when($startDate, function($query) use($startDate) {
                $query->where('date', '>=', $startDate);
            })
            ->when($endDate, function($query) use($endDate) {
                $query->where('date', '<=', $endDate);
            })
            ->when($type, function($query) use($type) {
                $query->where('type', $type);
            })
            ->where('project_id', $project->id)
            -get();
    }

}