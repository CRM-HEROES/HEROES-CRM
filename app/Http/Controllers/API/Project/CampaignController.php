<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Project;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->campaigns()
            ->with([
                'actions' => function($query) {
                    $query->withPivot('processing', 'error');
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
        abort_unless(auth()->user()->can('projectCampaignAdd', $project), 404);

        return $project
            ->campaigns()
            ->create(array_merge($request->only(
                'name',
                'description',
                'active',
                'for',
                'frequency',
                'date',
                'style',
                're_process_processed_prospects'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Campaign $campaign)
    {
        // Campaign associated to the current project
        abort_unless($project->id == $campaign->project_id, 404);

        return $campaign;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Campaign $campaign)
    {
        // Campaign associated to the current project
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless(auth()->user()->can('projectCampaignUpdate', $project), 404);

        $campaign->update($request->only(
            'name',
            'description',
            'active',
            'for',
            'frequency',
            'date',
            'style',
            're_process_processed_prospects'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Campaign $campaign)
    {
        // Campaign associated to the current project
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless(auth()->user()->can('projectCampaignDelete', $project), 404);

        $campaign->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Check rule error
     */
    public function checkRules(Project $project, Campaign $campaign)
    {
        // Campaign associated to the current project
        abort_unless($project->id == $campaign->project_id, 404);

        try {
            $campaign->matching();
            $campaign->update(['error' => false]);
        } catch (\Exception $e) {
            $campaign->update(['error' => true]);
            return response('Campaign rules error identified', 500);
        }

        return [
            'message' => "Campaign rules check finished."
        ];
    }
}
