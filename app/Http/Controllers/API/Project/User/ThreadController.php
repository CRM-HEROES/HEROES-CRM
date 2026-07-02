<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userThread', $project), 404);

        return $user
            ->threads()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Thread $thread)
    {
        abort_unless(auth()->user()->can('userThread', $project), 404);
        abort_unless($project->id == $thread->project_id, 404);

        return $thread
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Thread $thread)
    {
        abort_unless(auth()->user()->can('userThread', $project), 404);
        abort_unless($project->id == $thread->project_id, 404);

        $user
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
    public function destroy(Project $project, User $user, Thread $thread)
    {
        abort_unless(auth()->user()->can('userThread', $project), 404);
        abort_unless($project->id == $thread->project_id, 404);

        $user->threads()->detach($thread->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userThread', $project), 404);
        
        $this->validate($request, [
            'threads' => 'required',
            'users' => 'required',
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
                $this->bulkActionAttach($users, $threads);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $threads);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach threads
     */
    protected function bulkActionAttach(&$users, &$threads) {
        // Remove previous data
        $this->bulkActionDetach($users, $threads);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($thread) use($users, $now) {
            return array_map(function($user) use($thread, $now) {
                return [
                    'thread_id' => $thread,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $threads);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_thread')->insert($data);
    }

    /**
     * Bulk detach threads
     */
    protected function bulkActionDetach(&$users, &$threads) {
        DB::table('user_thread')
            ->whereIn('thread_id', $threads)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
