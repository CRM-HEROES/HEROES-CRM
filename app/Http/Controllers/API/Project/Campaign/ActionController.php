<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Http\Controllers\Controller;
use App\Models\CampaignAction;
use App\Models\Project;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->campaignActions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        return $project
            ->campaignActions()
            ->create(array_merge($request->only(
                'action',
                'value',
                'name',
                'style'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, CampaignAction $campaignAction)
    {
        abort_unless($project->id == $campaignAction->project_id, 404);

        return $campaignAction;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, CampaignAction $campaignAction)
    {
        abort_unless($project->id == $campaignAction->project_id, 404);

        $campaignAction->update($request->only(
            'value',
            'name',
            'action',
            'style'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, CampaignAction $campaignAction)
    {
        abort_unless($project->id == $campaignAction->project_id, 404);

        $campaignAction->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
