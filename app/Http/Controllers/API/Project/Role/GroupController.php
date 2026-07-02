<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->groups()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Group $group)
    {
        abort_unless($project->id == $group->project_id, 404);

        return $group
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Group $group)
    {
        abort_unless($project->id == $group->project_id, 404);

        $role
            ->groups()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$group->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Group $group)
    {
        abort_unless($project->id == $group->project_id, 404);

        $role->groups()->detach($group->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'groups' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only groups
        // associated to the current project
        $groups = $project
            ->groups()
            ->whereIn('id', $request->input('groups'))
            ->get(['id'])
            ->map(function($group) {
                return $group->id;
            })
            ->toArray();
            
        // Only roles
        // associated to the current project
        $roles = $project
            ->roles()
            ->whereIn('id', $request->input('roles'))
            ->get(['id'])
            ->map(function($role) {
                return $role->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($roles, $groups);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $groups);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach groups
     */
    protected function bulkActionAttach(&$roles, &$groups) {
        // Remove previous data
        $this->bulkActionDetach($roles, $groups);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($group) use($roles, $now) {
            return array_map(function($role) use($group, $now) {
                return [
                    'group_id' => $group,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $groups);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_group')->insert($data);
    }

    /**
     * Bulk detach groups
     */
    protected function bulkActionDetach(&$roles, &$groups) {
        DB::table('role_group')
            ->whereIn('group_id', $groups)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
