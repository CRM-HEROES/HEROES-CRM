<?php

namespace App\Http\Controllers\API\Project\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderStep;
use App\Models\Project;
use Illuminate\Http\Request;

class StepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->orderSteps()->orderBy('order')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->orderSteps()
            ->create(array_merge($request->only(
                'name',
                'description',
                'color',
                'bgcolor',
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, OrderStep $orderStep)
    {
        abort_unless($project->id == $orderStep->project_id, 404);

        return $orderStep;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, OrderStep $orderStep)
    {
        abort_unless($project->id == $orderStep->project_id, 404);

        $orderStep->update($request->only(
            'name',
            'description',
            'order',
            'color',
            'bgcolor',
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, OrderStep $orderStep)
    {
        abort_unless($project->id == $orderStep->project_id, 404);

        $orderStep->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
