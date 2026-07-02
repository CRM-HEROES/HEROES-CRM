<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userGroup', $project), 404);

        return $user
            ->groups()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Group $group)
    {
        abort_unless(auth()->user()->can('userGroup', $project), 404);
        abort_unless($project->id == $group->project_id, 404);

        return $group
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Group $group)
    {
        abort_unless(auth()->user()->can('userGroup', $project), 404);
        abort_unless($project->id == $group->project_id, 404);

        $user
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
    public function destroy(Project $project, User $user, Group $group)
    {
        abort_unless(auth()->user()->can('userGroup', $project), 404);
        abort_unless($project->id == $group->project_id, 404);

        $user->groups()->detach($group->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userGroup', $project), 404);
        
        $this->validate($request, [
            'groups' => 'required',
            'users' => 'required',
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
                $this->bulkActionAttach($users, $groups);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $groups);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach groups
     */
    protected function bulkActionAttach(&$users, &$groups) {
        // Remove previous data
        $this->bulkActionDetach($users, $groups);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($group) use($users, $now) {
            return array_map(function($user) use($group, $now) {
                return [
                    'group_id' => $group,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $groups);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_group')->insert($data);
    }

    /**
     * Bulk detach groups
     */
    protected function bulkActionDetach(&$users, &$groups) {
        DB::table('user_group')
            ->whereIn('group_id', $groups)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
