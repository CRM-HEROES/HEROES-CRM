<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Filters\ProspectFilters;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Project;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project, Campaign $campaign, ProspectFilters $prospectFilters)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($campaign->for == 'order', 404);

        $count = $request->input('count', 100);
        $page = $request->input('page', 0);

        return $campaign
            ->prospects()
            ->filter($prospectFilters)
            ->skip($count * $page)
            ->limit($count)
            ->get();
    }
    
    /**
     * Display a listing of the resource.
     */
    public function matching(Request $request, Project $project, Campaign $campaign)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($campaign->for == 'order', 404);

        $count = $request->input('count', 100);
        $page = $request->input('page', 0);

        return $campaign
            ->matching()
            ->whereDoesntHave('campaigns', function($query) use($campaign) {
                $query->where('campaigns.id', $campaign->id);
            })
            ->skip($count * $page)
            ->limit($count)
            ->get(['id']);
    }
}
