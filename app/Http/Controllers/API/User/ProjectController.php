<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {
        abort_unless(auth()->user()->is_super_admin || auth()->id() == $user->id, 404);

        $roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', '=', 'App\Models\User')
            ->where('model_has_roles.model_id', '=', $user->id)
            ->get(['roles.name', 'roles.project_id']);

        return $user
            ->projects()
            ->select('id', 'name', 'slug', 'logo', 'description', 'projects.creator_id')
            ->with('creator:id,name,last_name')
            ->get()
            ->transform(function ($project) use ($roles) {
                $project->roles = $roles->where('project_id', $project->id)->pluck('name');
                return $project;
            });
    }

    /**
     * Display associated users.
     */
    public function users(Project $project)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        return $project
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(User $user, Project $project)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        $user
            ->projects()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$project->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Project $project)
    {
        abort_unless(auth()->user()->is_super_admin, 404);

        $user->projects()->detach($project->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request)
    {
        abort_unless(auth()->user()->is_super_admin, 404);
        
        $this->validate($request, [
            'projects' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only projects
        // associated to the current project
        $projects = Project
            ::whereIn('id', $request->input('projects'))
            ->get(['id'])
            ->map(function($project) {
                return $project->id;
            })
            ->toArray();
            
        // Only users
        // associated to the current project
        $users =  User
            ::whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($user) {
                return $user->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $projects);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $projects);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach projects
     */
    protected function bulkActionAttach(&$users, &$projects) {
        // Remove previous data
        $this->bulkActionDetach($users, $projects);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($project) use($users, $now) {
            return array_map(function($user) use($project, $now) {
                return [
                    'project_id' => $project,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $projects);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_project')->insert($data);
    }

    /**
     * Bulk detach projects
     */
    protected function bulkActionDetach(&$users, &$projects) {
        DB::table('user_project')
            ->whereIn('project_id', $projects)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
