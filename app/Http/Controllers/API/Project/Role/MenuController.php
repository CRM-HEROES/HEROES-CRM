<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->menus()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Menu $menu)
    {
        abort_unless($project->id == $menu->project_id, 404);

        return $menu
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Menu $menu)
    {
        abort_unless($project->id == $menu->project_id, 404);

        $role
            ->menus()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$menu->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Menu $menu)
    {
        abort_unless($project->id == $menu->project_id, 404);

        $role->menus()->detach($menu->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'menus' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only menus
        // associated to the current project
        $menus = $project
            ->menus()
            ->whereIn('id', $request->input('menus'))
            ->get(['id'])
            ->map(function($menu) {
                return $menu->id;
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
                $this->bulkActionAttach($roles, $menus);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $menus);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach menus
     */
    protected function bulkActionAttach(&$roles, &$menus)
    {
        // Remove previous data
        $this->bulkActionDetach($roles, $menus);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($menu) use($roles, $now) {
            return array_map(function($role) use($menu, $now) {
                return [
                    'menu_id' => $menu,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $menus);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_menu')->insert($data);
    }

    /**
     * Bulk detach menus
     */
    protected function bulkActionDetach(&$roles, &$menus)
    {
        DB::table('role_menu')
            ->whereIn('menu_id', $menus)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
