<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);

        return $user
            ->categories()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Category $category)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        abort_unless($project->id == $category->project_id, 404);

        return $category
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Category $category)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        abort_unless($project->id == $category->project_id, 404);

        $user
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
    public function destroy(Project $project, User $user, Category $category)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        abort_unless($project->id == $category->project_id, 404);

        $user->categories()->detach($category->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userCategory', $project), 404);
        
        $this->validate($request, [
            'categories' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

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
                $this->bulkActionAttach($users, $categories);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $categories);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach categories
     */
    protected function bulkActionAttach(&$users, &$categories) {
        // Remove previous data
        $this->bulkActionDetach($users, $categories);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($category) use($users, $now) {
            return array_map(function($user) use($category, $now) {
                return [
                    'category_id' => $category,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $categories);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_category')->insert($data);
    }

    /**
     * Bulk detach categories
     */
    protected function bulkActionDetach(&$users, &$categories) {
        DB::table('user_category')
            ->whereIn('category_id', $categories)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
