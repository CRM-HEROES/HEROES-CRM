<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Models\Import;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class AiAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        return AiAgent::query()
            ->where('project_id', $project->id)
            ->whereIn('id', $import->ai_agents ?: [])
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Import $import, AiAgent $aiAgent)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $aiAgent->project_id, 404);
        abort_if($import->is_processing, 404);

        // Same lost-update race as UserGroupController::update() — the
        // "Relations" step fires one PUT per agent checkbox ticked, often
        // near-simultaneously, so reading+writing the already-loaded
        // $import->ai_agents array lets concurrent requests overwrite each
        // other. Lock the row for the read+write to serialize them.
        DB::transaction(function () use ($import, $aiAgent) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('ai_agents') ?: [];

            Import::whereKey($import->id)->update([
                'ai_agents' => array_unique(array_values(array_merge($current, [$aiAgent->id]))),
            ]);
        });

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, AiAgent $aiAgent)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $aiAgent->project_id, 404);
        abort_if($import->is_processing, 404);

        DB::transaction(function () use ($import, $aiAgent) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('ai_agents') ?: [];

            Import::whereKey($import->id)->update([
                'ai_agents' => array_values(array_filter($current, function ($agentId) use ($aiAgent) {
                    return $agentId != $aiAgent->id;
                })),
            ]);
        });

        return ['message' => trans('common.success.deleted_resource')];
    }
}
