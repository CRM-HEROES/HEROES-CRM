<?php

namespace App\Http\Controllers\API\Project;

use App\Exports\ProspectExport;
use App\Exports\ProspectDownload;
use App\Filters\ExportRequestFilters;
use App\Http\Controllers\Controller;
use App\Jobs\ExportProspects;
use App\Models\Export;
use App\Models\Project;
use App\Models\UserSetting;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;

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
        $request->validate([
            'mode' => ['sometimes', 'required', 'in:email,download'],
            'count' => ['sometimes', 'integer', 'min:0'],
            'filters' => ['sometimes', 'json'],
            'email' => ['exclude_unless:mode,email', 'required', 'email', 'max:255'],
        ]);

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
        
        // Keep the existing behavior for callers that do not specify a mode.
        $mode = $request->input('mode', empty($export->filters) ? 'email' : 'download');

        if ($mode === 'email') {
            dispatch((new ExportProspects($export))->onQueue('imports'));

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre export est en cours de préparation. Vous le recevrez par email.',
                ], 202);
            }

            return back();
        }

        if ($request->input('mode') === 'download') {
            app(ProspectDownload::class)->enqueue($export);

            return response()->json([
                'message' => 'Préparation du fichier Excel en cours…',
                'status_url' => route('api.project.export.status', [$project, $export], false),
                'download_url' => route('api.project.export.download', [$project, $export], false),
            ], 202);
        }

        return app(ProspectDownload::class)->download(new ProspectExport($export, $this->getExportLimit($export)), $project->name . '.xlsx');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Export $export)
    {
        // Export associated to the current project
        abort_unless($project->id == $export->project_id, 404);

        if (app(ProspectDownload::class)->status($export)['status'] !== 'missing') {
            return $this->download($project, $export);
        }

        // Send the export by email
        // if no filters is applied
        if (empty($export->filters)) {
            dispatch((new ExportProspects($export))->onQueue('imports'));
            return back();
        } else {
            return app(ProspectDownload::class)->download(new ProspectExport($export, $this->getExportLimit($export)), $project->name . '.xlsx');
        }
    }

    public function status(Project $project, Export $export)
    {
        $this->authorizeDownload($project, $export);

        return app(ProspectDownload::class)->status($export);
    }

    public function download(Project $project, Export $export)
    {
        $this->authorizeDownload($project, $export);

        return app(ProspectDownload::class)->preparedFile($export);
    }

    protected function authorizeDownload(Project $project, Export $export): void
    {
        abort_unless($project->id == $export->project_id, 404);
        abort_unless($export->creator_id == auth()->id(), 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Export $export)
    {
        // Export associated to the current project
        abort_unless($project->id == $export->project_id, 404);

        app(ProspectDownload::class)->delete($export);
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

    /**
     * Get export limit
     */
    protected function getExportLimit(Export $export)
    {
        return (int) $export->count;
    }
}
