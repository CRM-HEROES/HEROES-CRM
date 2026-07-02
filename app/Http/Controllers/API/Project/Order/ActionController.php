<?php

namespace App\Http\Controllers\API\Project\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderAction;
use App\Models\Project;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->orderActions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(!$request->has('default_role_id') || $project->roles()->find($request->input('default_role_id')), 404, 'Unknown role');

        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->orderActions()
            ->create(array_merge($request->only(
                'name',
                'description',
                'color',
                'bgcolor',
                'default_role_id',
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, OrderAction $orderAction)
    {
        abort_unless($project->id == $orderAction->project_id, 404);

        return $orderAction;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, OrderAction $orderAction)
    {
        abort_unless($project->id == $orderAction->project_id, 404);
        abort_unless(!$request->has('default_role_id') || $project->roles()->find($request->input('default_role_id')), 404, 'Unknown role');

        $orderAction->update($request->only(
            'name',
            'description',
            'order',
            'color',
            'bgcolor',
            'default_role_id',
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, OrderAction $orderAction)
    {
        abort_unless($project->id == $orderAction->project_id, 404);

        $orderAction->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
