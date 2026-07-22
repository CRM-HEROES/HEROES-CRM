<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Role;
use App\Models\Project;

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

        $import->update([
            'roles' => array_unique(array_values(array_merge($import->roles ?: [], [$role->id])))
        ]);

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

        $import->update([
            'roles' => array_values(array_filter($import->roles ?: [], function($roleId) use($role) {
                return $roleId != $role->id;
            }))
        ]);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
