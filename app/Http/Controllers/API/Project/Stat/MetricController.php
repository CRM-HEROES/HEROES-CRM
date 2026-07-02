<?php

namespace App\Http\Controllers\API\Project\Stat;

use App\Http\Controllers\Controller;
use App\Models\Metric;
use App\Models\Project;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->metrics()
            ->get();
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
            ->metrics()
            ->create(array_merge($request->only(
                'name',
                'filters',
                'for',
                'color',
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Metric $metric)
    {
        abort_unless($project->id == $metric->project_id, 404);

        return $metric;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Metric $metric)
    {
        abort_unless($project->id == $metric->project_id, 404);

        $metric->update($request->only(
            'name',
            'filters',
            'for',
            'color',
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Metric $metric)
    {
        abort_unless($project->id == $metric->project_id, 404);

        $metric->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
