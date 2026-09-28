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

        DB::transaction(function () use ($import, $aiAgent) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('ai_agents') ?: [];
            $updated = array_unique(array_values(array_merge($current, [$aiAgent->id])));

            // Save via the model so Eloquent observers fire, which in turn
            // dispatch the ImportAiAgentObserver and LaunchImportAiCallsJob.
            $import->forceFill([
                'ai_agents' => $updated,
            ]);
            $import->save();
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
            $updated = array_values(array_filter($current, function ($agentId) use ($aiAgent) {
                return (int) $agentId !== (int) $aiAgent->id;
            }));

            $import->forceFill([
                'ai_agents' => $updated,
            ]);
            $import->save();
        });

        return ['message' => trans('common.success.deleted_resource')];
    }
}
