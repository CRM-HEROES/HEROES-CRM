<?php

namespace App\Http\Controllers\API\Project\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\Project;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->orderStatuses;
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
            ->orderStatuses()
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
    public function show(Project $project, OrderStatus $orderStatus)
    {
        abort_unless($project->id == $orderStatus->project_id, 404);

        return $orderStatus;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, OrderStatus $orderStatus)
    {
        abort_unless($project->id == $orderStatus->project_id, 404);

        $orderStatus->update($request->only(
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
    public function destroy(Project $project, OrderStatus $orderStatus)
    {
        abort_unless($project->id == $orderStatus->project_id, 404);

        $orderStatus->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
