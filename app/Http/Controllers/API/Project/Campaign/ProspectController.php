<?php

namespace App\Http\Controllers\API\Project\Campaign;

use App\Filters\ProspectRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Project;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project, Campaign $campaign, ProspectRequestFilters $prospectFilters)
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
    public function matching(Request $request, Project $project, Campaign $campaign, ProspectRequestFilters $prospectFilters)
    {
        abort_unless($project->id == $campaign->project_id, 404);
        abort_unless($campaign->for == 'prospect', 404);

        $count = $request->input('count', 100);
        $page = $request->input('page', 1) - 1;

        return $campaign
            ->matching()
            ->whereDoesntHave('campaigns', function($query) use($campaign) {
                $query->where('campaigns.id', $campaign->id);
            })
            ->filter($prospectFilters)
            ->skip($count * $page)
            ->limit($count)
            ->get(['id', 'full_name']);
    }
}
