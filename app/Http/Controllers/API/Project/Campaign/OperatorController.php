<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Http\Controllers\Controller;
use App\Models\CampaignOperator;
use App\Models\Project;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->campaignOperators()
            ->with([
                'campaigns' => function($query) {
                    // return $query->select('id');
                },
                'parentOperators' => function($query) {
                    // return $query->select('id');
                }
            ])
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        return $project
            ->campaignOperators()
            ->create(array_merge($request->only(
                'and',
                'style'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, CampaignOperator $campaignOperator)
    {
        abort_unless($project->id == $campaignOperator->project_id, 404);

        return $campaignOperator;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, CampaignOperator $campaignOperator)
    {
        abort_unless($project->id == $campaignOperator->project_id, 404);

        $campaignOperator->update($request->only(
            'and',
            'style'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, CampaignOperator $campaignOperator)
    {
        abort_unless($project->id == $campaignOperator->project_id, 404);

        $campaignOperator->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
