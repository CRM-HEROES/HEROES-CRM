<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Http\Controllers\Controller;
use App\Models\CampaignRule;
use App\Models\Project;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->campaignRules()
            ->with([
                'campaigns' => function($query) {
                    $query->withPivot('error');
                    // return $query->select('id');
                },
                'operators' => function($query) {
                    $query->withPivot('error');
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
            ->campaignRules()
            ->create(array_merge($request->only(
                'name',
                'type',
                'rule',
                'style'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, CampaignRule $campaignRule)
    {
        abort_unless($project->id == $campaignRule->project_id, 404);

        return $campaignRule;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, CampaignRule $campaignRule)
    {
        abort_unless($project->id == $campaignRule->project_id, 404);

        $campaignRule->update($request->only(
            'name',
            'style'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, CampaignRule $campaignRule)
    {
        abort_unless($project->id == $campaignRule->project_id, 404);

        $campaignRule->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
