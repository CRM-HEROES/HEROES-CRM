<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->threads()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Thread $thread)
    {
        abort_unless($project->id == $thread->project_id, 404);

        return $thread
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Thread $thread)
    {
        abort_unless($project->id == $thread->project_id, 404);

        $role
            ->threads()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$thread->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Thread $thread)
    {
        abort_unless($project->id == $thread->project_id, 404);

        $role->threads()->detach($thread->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'threads' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only threads
        // associated to the current project
        $threads = $project
            ->threads()
            ->whereIn('id', $request->input('threads'))
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
                $this->bulkActionAttach($roles, $threads);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $threads);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach threads
     */
    protected function bulkActionAttach(&$roles, &$threads) {
        // Remove previous data
        $this->bulkActionDetach($roles, $threads);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($thread) use($roles, $now) {
            return array_map(function($role) use($thread, $now) {
                return [
                    'thread_id' => $thread,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $threads);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_thread')->insert($data);
    }

    /**
     * Bulk detach threads
     */
    protected function bulkActionDetach(&$roles, &$threads) {
        DB::table('role_thread')
            ->whereIn('thread_id', $threads)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
