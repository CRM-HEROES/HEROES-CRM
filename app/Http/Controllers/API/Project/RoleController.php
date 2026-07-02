<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\RoleRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->roles()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectRoleAdd', $project), 404);

        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->roles()
            ->create(array_merge($request->only(
                'name',
            ), [
                'guard_name' => "web",
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Role $role)
    {
        abort_unless($project->id == $role->project_id, 404);

        $role->load([
            'calendars' => function($query) {
                $query->select('id', 'name');
            },
            'categories' => function($query) {
                $query->select('id', 'name');
            },
            'documents' => function($query) {
                $query->select('id', 'name');
            },
            'folders' => function($query) {
                $query->select('id', 'name');
            },
            'orderActions' => function($query) {
                $query->select('id', 'name');
            },
            'orderSteps' => function($query) {
                $query->select('id', 'name');
            },
            'questionnaires' => function($query) {
                $query->select('id', 'name');
            },
            'threads' => function($query) {
                $query->select('id', 'name');
            },
            'assignableUsers' => function($query) {
                $query->select('id', 'name');
            },
        ]);
        
        return $role;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Role $role)
    {
        abort_unless(auth()->user()->can('projectRoleUpdate', $project), 404);
        abort_unless($project->id == $role->project_id, 404);

        $role->update($request->only(
            'name'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role)
    {
        abort_unless(auth()->user()->can('projectRoleDelete', $project), 404);
        abort_unless($project->id == $role->project_id, 404);

        $role->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
