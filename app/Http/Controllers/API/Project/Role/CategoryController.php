<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->categories()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Category $category)
    {
        abort_unless($project->id == $category->project_id, 404);

        return $category
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Category $category)
    {
        abort_unless($project->id == $category->project_id, 404);

        $role
            ->categories()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$category->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Category $category)
    {
        abort_unless($project->id == $category->project_id, 404);

        $role->categories()->detach($category->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'categories' => 'required',
            'roles' => 'required',
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
            
        // Only categories
        // associated to the current project
        $categories = $project
            ->categories()
            ->whereIn('id', $request->input('categories'))
            ->get(['id'])
            ->map(function($category) {
                return $category->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($roles, $categories);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $categories);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach categories
     */
    protected function bulkActionAttach(&$roles, &$categories) {
        // Remove previous data
        $this->bulkActionDetach($roles, $categories);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($category) use($roles, $now) {
            return array_map(function($role) use($category, $now) {
                return [
                    'category_id' => $category,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $categories);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_category')->insert($data);
    }

    /**
     * Bulk detach categories
     */
    protected function bulkActionDetach(&$roles, &$categories) {
        DB::table('role_category')
            ->whereIn('category_id', $categories)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
