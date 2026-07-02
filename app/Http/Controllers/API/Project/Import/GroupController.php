<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Models\Group;
use App\Models\Project;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);
        
        return $project
            ->groups()
            ->whereIn('id', $import->groups ?: [])
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

        $import->update([
            'groups' => array_unique(array_values(array_merge($import->groups ?: [], [$group->id])))
        ]);

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

        $import->update([
            'groups' => array_values(array_filter($import->groups ?: [], function($groupId) use($group) {
                return $groupId != $group->id;
            }))
        ]);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
