<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->folders()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Folder $folder)
    {
        abort_unless($project->id == $folder->project_id, 404);

        return $folder
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Folder $folder)
    {
        abort_unless($project->id == $folder->project_id, 404);

        $role
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
    public function destroy(Project $project, Role $role, Folder $folder)
    {
        abort_unless($project->id == $folder->project_id, 404);

        $role->folders()->detach($folder->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'folders' => 'required',
            'roles' => 'required',
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
                $this->bulkActionAttach($roles, $folders);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $folders);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach folders
     */
    protected function bulkActionAttach(&$roles, &$folders) {
        // Remove previous data
        $this->bulkActionDetach($roles, $folders);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($folder) use($roles, $now) {
            return array_map(function($role) use($folder, $now) {
                return [
                    'folder_id' => $folder,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $folders);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_folder')->insert($data);
    }

    /**
     * Bulk detach folders
     */
    protected function bulkActionDetach(&$roles, &$folders) {
        DB::table('role_folder')
            ->whereIn('folder_id', $folders)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
