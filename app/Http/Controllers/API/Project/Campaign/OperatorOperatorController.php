<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Http\Controllers\Controller;
use App\Models\CampaignOperator;
use App\Models\Project;

class OperatorOperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, CampaignOperator $operator)
    {
        abort_unless($project->id == $operator->project_id, 404);

        return $operator->operators;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, CampaignOperator $parent, CampaignOperator $child)
    {
        abort_unless($project->id == $parent->project_id, 404);
        abort_unless($project->id == $child->project_id, 404);

        $parent
            ->operators()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$child->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, CampaignOperator $parent, CampaignOperator $child)
    {
        abort_unless($project->id == $parent->project_id, 404);
        abort_unless($project->id == $child->project_id, 404);

        $parent->operators()->detach($child->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
