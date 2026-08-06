<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Role;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);

        return $project
            ->roles()
            ->whereIn('id', $import->roles ?: [])
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Import $import, Role $role)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $role->project_id, 404);
        abort_if($import->is_processing, 404);

        // Same lost-update race as UserController::update() — the
        // "Relations" step fires one PUT per role checkbox ticked, often
        // near-simultaneously, so reading+writing the already-loaded
        // $import->roles array lets concurrent requests overwrite each
        // other. Lock the row for the read+write to serialize them.
        DB::transaction(function () use ($import, $role) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('roles') ?: [];

            Import::whereKey($import->id)->update([
                'roles' => array_unique(array_values(array_merge($current, [$role->id]))),
            ]);
        });

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Import $import, Role $role)
    {
        abort_unless($project->id == $import->project_id, 404);
        abort_unless($project->id == $role->project_id, 404);
        abort_if($import->is_processing, 404);

        DB::transaction(function () use ($import, $role) {
            $current = Import::whereKey($import->id)->lockForUpdate()->value('roles') ?: [];

            Import::whereKey($import->id)->update([
                'roles' => array_values(array_filter($current, function ($roleId) use ($role) {
                    return $roleId != $role->id;
                })),
            ]);
        });

        return ['message' => trans('common.success.deleted_resource')];
    }
}
