<?php

namespace App\Http\Controllers\API\Project;

use App\Exports\ProspectExport;
use App\Filters\ExportRequestFilters;
use App\Http\Controllers\Controller;
use App\Jobs\ExportProspects;
use App\Models\Export;
use App\Models\Project;
use App\Models\UserSetting;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, ExportRequestFilters $exportRequestFilters)
    {
        return $project
            ->exports()
            ->filter($exportRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(Request $request, Project $project)
    {
        $fields = $this->getFields($request, $project);

        // Before exporting
        // Add trace into the DB
        $export = $project->exports()->create([
            'filters' => json_decode($request->input('filters', '{}')),
            'fields' => $fields,
            'sort_by' => $request->input('sort_by', 'id'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'count' => $request->input('count', 0),
            'email' => $request->input('email', auth()->user()->email),
            'creator_id' => auth()->id(),
        ]);
        
        // Send the export by email
        // if no filters is applied
        if (empty($export->filters)) {
            dispatch((new ExportProspects($export))->onQueue('imports'));
            return back();
        } else {
            return Excel::download(new ProspectExport($export), $project->name . '.xlsx');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Export $export)
    {
        // Export associated to the current project
        abort_unless($project->id == $export->project_id, 404);

        // Send the export by email
        // if no filters is applied
        if (empty($export->filters)) {
            dispatch((new ExportProspects($export))->onQueue('imports'));
            return back();
        } else {
            return Excel::download(new ProspectExport($export), $project->name . '.xlsx');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Export $export)
    {
        // Export associated to the current project
        abort_unless($project->id == $export->project_id, 404);

        $export->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Get fields
     */
    protected function getFields(Request $request, Project $project)
    {
        // From params
        if ($request->has('fields')) {
            return explode(',', $request->input('fields'));
        }
        
        // From user settings
        $userSetting = UserSetting::where([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'key' => 'prospects-table',
        ])->first();

        if ($userSetting) {
            return array_map(function($field) {
                return $field['key'];
            }, $userSetting->value);
        }

        // From project settings
        /*$projectSetting = ProjectSetting::get($project, 'prospects-table');

        if ($projectSetting) {
            return $projectSetting->map(function($field) {
                return $field['key'];
            });
        }*/

        // Default
        return array_merge(
            $project
                ->fields()
                ->where('for', "prospect")
                ->where('meta', false)
                ->get()
                ->pluck('slug')
                ->toArray(), [
                    'events', 'users'
                ]
        );
    }
}
