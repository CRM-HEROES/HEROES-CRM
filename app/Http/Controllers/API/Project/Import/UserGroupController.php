<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Import;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class UserGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        return $project
            ->groups()
            ->whereIn('id', $import->user_groups ?: [])
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Import $import, Group $group)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $group->project_id, 404);
        abort_if($import->is_processing, 404);

        // Same lost-update race as RoleController::update() — the
        // "Relations" step fires one PUT per checkbox ticked, often
        // near-simultaneously, so reading+writing the already-loaded
        // $import->user_groups array lets concurrent requests overwrite
        // each other. Lock the row for the read+write to serialize them.
        DB::transaction(function () use ($import, $group) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('user_groups') ?: [];

            Import::whereKey($import->id)->update([
                'user_groups' => array_unique(array_values(array_merge($current, [$group->id]))),
            ]);
        });

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, Group $group)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $group->project_id, 404);
        abort_if($import->is_processing, 404);

        DB::transaction(function () use ($import, $group) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('user_groups') ?: [];

            Import::whereKey($import->id)->update([
                'user_groups' => array_values(array_filter($current, function ($groupId) use ($group) {
                    return $groupId != $group->id;
                })),
            ]);
        });

        return ['message' => trans('common.success.deleted_resource')];
    }
}
