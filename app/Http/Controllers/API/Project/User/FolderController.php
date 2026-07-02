<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userFolder', $project), 404);

        return $user
            ->folders()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Folder $folder)
    {
        abort_unless(auth()->user()->can('userFolder', $project), 404);
        abort_unless($project->id == $folder->project_id, 404);

        return $folder
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Folder $folder)
    {
        abort_unless(auth()->user()->can('userFolder', $project), 404);
        abort_unless($project->id == $folder->project_id, 404);

        $user
            ->folders()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$folder->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Folder $folder)
    {
        abort_unless(auth()->user()->can('userFolder', $project), 404);
        abort_unless($project->id == $folder->project_id, 404);

        $user->folders()->detach($folder->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userFolder', $project), 404);
        
        $this->validate($request, [
            'folders' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only folders
        // associated to the current project
        $folders = $project
            ->folders()
            ->whereIn('id', $request->input('folders'))
            ->get(['id'])
            ->map(function($folder) {
                return $folder->id;
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
                $this->bulkActionAttach($users, $folders);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $folders);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach folders
     */
    protected function bulkActionAttach(&$users, &$folders) {
        // Remove previous data
        $this->bulkActionDetach($users, $folders);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($folder) use($users, $now) {
            return array_map(function($user) use($folder, $now) {
                return [
                    'folder_id' => $folder,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $folders);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_folder')->insert($data);
    }

    /**
     * Bulk detach folders
     */
    protected function bulkActionDetach(&$users, &$folders) {
        DB::table('user_folder')
            ->whereIn('folder_id', $folders)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
