<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->assignableUsers()
            ->select('id', 'name')
            ->wherePivot('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, User $assignableUser)
    {
        abort_unless($project->users()->find($assignableUser->id), 404);

        return $assignableUser
            ->assigningRoles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, User $assignableUser)
    {
        abort_unless($project->users()->find($assignableUser->id), 404);

        DB::table('role_assignable_user')
            ->updateOrInsert([
                'role_id' => $role->id,
                'assignable_user_id' => $assignableUser->id,
            ], [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, User $assignableUser)
    {
        abort_unless($project->id == $assignableUser->project_id, 404);

        DB::table('role_assignable_user')
            ->where([
                'role_id' => $role->id,
                'assignable_user_id' => $assignableUser->id,
                'project_id' => $project->id,
            ])
            ->delete();

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'assignableUsers' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only roles
        // associated to the current project
        $assignableUsers = $project
            ->roles()
            ->whereIn('id', $request->input('assignableUsers'))
            ->get(['id'])
            ->map(function($thread) {
                return $thread->id;
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
                $this->bulkActionAttach($project, $roles, $assignableUsers);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($project, $roles, $assignableUsers);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach assignable roles
     */
    protected function bulkActionAttach(Project $project, &$roles, &$assignableUsers) {
        // Remove previous data
        $this->bulkActionDetach($project, $roles, $assignableUsers);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($assignableUser) use($project, $roles, $now) {
            return array_map(function($role) use($project, $assignableUser, $now) {
                return [
                    'role_assignable_id' => $assignableUser,
                    'role_id' => $role,
                    'project_id' => $project->id,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $assignableUsers);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_assignable_user')->insert($data);
    }

    /**
     * Bulk detach assignable roles
     */
    protected function bulkActionDetach(Project $project, &$roles, &$assignableUsers) {
        DB::table('role_assignable_user')
            ->whereIn('role_assignable_id', $assignableUsers)
            ->whereIn('role_id', $roles)
            ->where('project_id', $project->id)
            ->delete();
    }
}
