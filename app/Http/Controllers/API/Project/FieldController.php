<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\FieldRequestFilters;
use App\Http\Controllers\Controller;
use App\Jobs\FieldToCategory;
use App\Models\Field;
use App\Models\Project;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, FieldRequestFilters $fieldRequestFilters)
    {
        return $project
            ->fields()
            ->filter($fieldRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectFieldAdd', $project), 404);
        // If field is not meta
        // Check if field is among
        // default given fields
        abort_unless(
            $request->input('meta', false) || 
            count(
                array_filter(
                    \App\Utils\Field\Field::fields(), 
                    function($field) use($request) {
                        return 
                            $field['slug'] == $request->input('slug', '') && 
                            $field['for'] == $request->input('for', 'prospect');
                    }
                )
            )
        , 404);

        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->fields()
            ->create(array_merge($request->only(
                'slug',
                'name',
                'description',
                'type',
                'for',
                'default_value',
                'format',
                'searchable',
                'required',
                'unique',
                'labeled',
                'meta'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Field $field)
    {
        // Field associated to the current project
        abort_unless($project->id == $field->project_id, 404);

        return $field;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Field $field)
    {
        // Field associated to the current project
        abort_unless($project->id == $field->project_id, 404);
        abort_unless(auth()->user()->can('projectFieldUpdate', $project), 404);

        $field->update($request->only(
            'name',
            'type',
            'description',
            'default_value',
            'format',
            'required',
            'unique',
            'searchable',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Field $field)
    {
        // Field associated to the current project
        abort_unless($project->id == $field->project_id, 404);
        abort_unless(auth()->user()->can('projectFieldDelete', $project), 404);

        $field->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Transform field
     * to category
     */
    public function category(Project $project, Field $field)
    {
        abort_unless(auth()->user()->can('projectFieldUpdate', $project), 404);

        // Create a new category
        // having the same name as the field
        if (!($category = $project
            ->categories()
            ->where('name', $field->name)
            ->first())
        ) {
            $category = $project
                ->categories()
                ->create(['name' => $field->name]);
        }
        
        // Job converting all prospects infos related to the field
        // to labels that will be created inside the category
        dispatch((new FieldToCategory($field, $category))->onQueue('imports'));

        return $category;
    }

    /**
     * 
     */
    public function orders(Request $request, Project $project) 
    {
        abort_unless(auth()->user()->can('projectFieldUpdate', $project), 404);

        $this->validate($request, [
            'orders' => 'required',
        ]);

        $fields = $project->fields;

        foreach ($request->input('orders') as $order) {
            $field = $fields->where('id', $order['field'])->first();

            if ($field) {
                $field->update([
                    'order' => $order['order']
                ]);
            }
        }

        return ['message' => trans('common.success.updated_resource')];
    }
    
}
