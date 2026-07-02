<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignAction;
use App\Models\Project;

class CampaignActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Campaign $campaign)
    {
        abort_unless($project->id == $campaign->project_id, 404);

        return $campaign->actions;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Campaign $campaign, CampaignAction $action)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($project->id == $action->project_id, 404);

        $campaign
            ->actions()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$action->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Campaign $campaign, CampaignAction $action)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($project->id == $action->project_id, 404);

        $campaign->actions()->detach($action->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
