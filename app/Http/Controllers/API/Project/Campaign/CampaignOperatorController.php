<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignOperator;
use App\Models\Project;

class CampaignOperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Campaign $campaign)
    {
        abort_unless($project->id == $campaign->project_id, 404);

        return $campaign->operators;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Campaign $campaign, CampaignOperator $operator)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($project->id == $operator->project_id, 404);

        $campaign
            ->operators()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$operator->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Campaign $campaign, CampaignOperator $operator)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($project->id == $operator->project_id, 404);

        $campaign->operators()->detach($operator->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
