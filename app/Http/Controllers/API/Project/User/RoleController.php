<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userRole', $project), 404);

        return $user
            ->roles()
            ->select('id', 'name')
            ->where('roles.project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Role $role)
    {
        abort_unless(auth()->user()->can('userRole', $project), 404);
        abort_unless($project->id == $role->project_id, 404);

        return $role
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Role $role)
    {
        abort_unless(auth()->user()->can('userRole', $project), 404);
        abort_unless($project->id == $role->project_id, 404);

        $user
            ->roles()
            ->syncWithoutDetaching([$role->id => [
                'project_id' => $project->id
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Role $role)
    {
        abort_unless(auth()->user()->can('userRole', $project), 404);
        abort_unless($project->id == $role->project_id, 404);

        $user->roles()->detach($role->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userRole', $project), 404);
        
        $this->validate($request, [
            'roles' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

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
            
        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($user) {
                return $user->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $roles);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $roles);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach roles
     */
    protected function bulkActionAttach(&$users, &$roles) {
        // Remove previous data
        $this->bulkActionDetach($users, $roles);

        // Update datas
        $data = array_map(function($role) use($users) {
            return array_map(function($user) use($role) {
                return [
                    'role_id' => $role,
                    'model_type' => "App\User",
                    'model_id' => $user,
                ];
            }, $users);
        }, $roles);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table(config('permission.table_names')['model_has_roles'])->insert($data);
    }

    /**
     * Bulk detach roles
     */
    protected function bulkActionDetach(&$users, &$roles) {
        DB::table(config('permission.table_names')['model_has_roles'])
            ->whereIn('role_id', $roles)
            ->where('model_type', "App\User")
            ->whereIn('model_id', $users)
            ->delete();
    }
}
