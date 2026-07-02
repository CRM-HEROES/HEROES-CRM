<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\CategoryRequestFilters;
use App\Http\Controllers\Controller;
use App\Jobs\CategoryToField;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, CategoryRequestFilters $categoryRequestFilters)
    {
        return $project
            ->categories()
            ->orderBy('relevance')
            ->orderBy('relevance', 'desc')
            ->orderBy('name')
            ->with([
                'labels' => function($query) {
                    $query
                        ->orderBy('relevance', 'desc')
                        ->orderBy('name');
                }
            ])
            ->filter($categoryRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectCategoryAdd', $project), 404);
        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->categories()
            ->create(array_merge($request->only(
                'name',
                'description',
                'for',
                'color',
                'bgcolor',
                'unique'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Category $category)
    {
        // Category associated to the current project
        abort_unless($project->id == $category->project_id, 404);

        return $category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Category $category)
    {
        // Category associated to the current project
        abort_unless($project->id == $category->project_id, 404);
        abort_unless(auth()->user()->can('projectCategoryUpdate', $project), 404);

        $category->update($request->only(
            'name',
            'description',
            'for',
            'color',
            'bgcolor',
            'order',
            'unique'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Category $category)
    {
        // Category associated to the current project
        abort_unless($project->id == $category->project_id, 404);
        abort_unless(auth()->user()->can('projectCategoryDelete', $project), 404);

        $category->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * 
     */
    public function combine(Project $project, Category $category, Category $category2)
    {
        abort_unless($project->id == $category->project_id, 404);
        abort_unless($project->id == $category2->project_id, 404);
        abort_unless(auth()->user()->can('projectCategoryUpdate', $project), 404);

        DB::update('update labels set category_id = ? where category_id = ?', [$category2->id, $category->id]);

        if ($category->labels->count() == 0) {
            $category->delete();
        }

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * 
     */
    public function field(Project $project, Category $category)
    {
        abort_unless($project->id == $category->project_id, 404);
        abort_unless(auth()->user()->can('projectCategoryUpdate', $project), 404);

        if (!($field = $project
            ->fields()
            ->where('name', $category->name)
            ->where('meta', true)
            ->first())
        ) {
            $field = $project
                ->fields()
                ->create([
                    'name' => $category->name, 
                    // 'type' => 'long_text', 
                    'meta' => true
                ]);
        }

        dispatch((new CategoryToField($category, $field))->onQueue('imports'));

        $field->refresh();

        return $field;
    }

    /**
     * 
     */
    public function orders(Request $request, Project $project) 
    {
        abort_unless(auth()->user()->can('projectCategoryUpdate', $project), 404);

        $this->validate($request, [
            'orders' => 'required',
        ]);

        $categories = $project->categories;

        foreach ($request->input('orders') as $order) {
            $category = $categories->where('id', $order['category'])->first();

            if ($category) {
                $category->update([
                    'order' => $order['order']
                ]);
            }
        }

        return ['message' => trans('common.success.updated_resource')];
    }
    
}
