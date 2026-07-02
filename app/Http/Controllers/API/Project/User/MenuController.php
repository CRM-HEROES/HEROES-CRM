<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userMenu', $project) || $user->id == auth()->id(), 404);

        return $user
            ->menus()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Menu $menu)
    {
        abort_unless(auth()->user()->can('userMenu', $project), 404);
        abort_unless($project->id == $menu->project_id, 404);

        return $menu
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, User $user, Menu $menu)
    {
        abort_unless(
            auth()->user()->can('userMenu', $project) || 
            $user->id == auth()->id() && $menu->creator_id == auth()->id() || 
            $user->menus()->where('id', $menu->id)->first(), 
            404
        );
        abort_unless($project->id == $menu->project_id, 404);

        if ($request->input('default', false)) {
            foreach ($user->menus as $m) {
                $user->menus()->updateExistingPivot($m->id, ['default' => 0]);
            }
        }

        $user
            ->menus()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$menu->id => array_merge([
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ], $request->only('default'))]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Menu $menu)
    {
        abort_unless(auth()->user()->can('userMenu', $project) || ($user->id == auth()->id() && $menu->creator_id == auth()->id()), 404);
        abort_unless($project->id == $menu->project_id, 404);

        $user->menus()->detach($menu->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userMenu', $project), 404);

        $this->validate($request, [
            'menus' => 'required',
            'users' => 'required',
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
                $this->bulkActionAttach($users, $menus);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $menus);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach menus
     */
    protected function bulkActionAttach(&$users, &$menus)
    {
        // Remove previous data
        $this->bulkActionDetach($users, $menus);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($menu) use($users, $now) {
            return array_map(function($user) use($menu, $now) {
                return [
                    'menu_id' => $menu,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $menus);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_menu')->insert($data);
    }

    /**
     * Bulk detach menus
     */
    protected function bulkActionDetach(&$users, &$menus)
    {
        DB::table('user_menu')
            ->whereIn('menu_id', $menus)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
