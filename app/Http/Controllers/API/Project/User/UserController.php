<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userUser', $project), 404);

        return $user
            ->assignableUsers()
            ->select('id', 'name')
            ->wherePivot('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, User $assignableUser)
    {
        abort_unless(auth()->user()->can('userUser', $project), 404);
        abort_unless($project->users()->find($assignableUser->id), 404);

        return $assignableUser
            ->assigningUsers()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, User $assignableUser)
    {
        abort_unless(auth()->user()->can('userUser', $project), 404);
        abort_unless($project->users()->find($assignableUser->id), 404);

        DB::table('user_assignable_user')
            ->updateOrInsert([
                'user_id' => $user->id,
                'assignable_user_id' => $assignableUser->id,
                'project_id' => $project->id,
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
    public function destroy(Project $project, User $user, User $assignableUser)
    {
        abort_unless(auth()->user()->can('userUser', $project), 404);
        abort_unless($user->assignableUsers->where('pivot.project_id', $project->id)->where('pivot.assignable_user_id', $assignableUser->id)->first(), 404);

        DB::table('user_assignable_user')
            ->where([
                'user_id' => $user->id,
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
        abort_unless(auth()->user()->can('userUser', $project), 404);

        $this->validate($request, [
            'assignableUsers' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only users
        // associated to the current project
        $assignableUsers = $project
            ->users()
            ->whereIn('id', $request->input('assignableUsers'))
            ->get(['id'])
            ->map(function($thread) {
                return $thread->id;
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
                $this->bulkActionAttach($project, $users, $assignableUsers);
                return ['message' => trans('common.success.updated_resource')];

            case "detach":
                $this->bulkActionDetach($project, $users, $assignableUsers);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach assignable users
     */
    protected function bulkActionAttach(Project $project, &$users, &$assignableUsers) {
        // Remove previous data
        $this->bulkActionDetach($project, $users, $assignableUsers);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($assignableUser) use($project, $users, $now) {
            return array_map(function($user) use($project, $assignableUser, $now) {
                return [
                    'assignable_user_id' => $assignableUser,
                    'user_id' => $user,
                    'project_id' => $project->id,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $assignableUsers);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_assignable_user')->insert($data);
    }

    /**
     * Bulk detach assignable users
     */
    protected function bulkActionDetach(Project $project, &$users, &$assignableUsers) {
        DB::table('user_assignable_user')
            ->whereIn('assignable_user_id', $assignableUsers)
            ->whereIn('user_id', $users)
            ->where('project_id', $project->id)
            ->delete();
    }
}
