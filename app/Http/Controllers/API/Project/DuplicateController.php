<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Jobs\CheckDuplicatedProspects;
use App\Jobs\HandleDuplicatedProspects;
use App\Models\Project;
use Illuminate\Http\Request;

class DuplicateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function find(Request $request, Project $project)
    {
        $this->validate($request, [
            'fields' => 'required',
        ]);

        dispatch((new CheckDuplicatedProspects(
            $project, 
            $request->input('fields')
        ))->onConnection('sync'));
    }
    
    /**
     * Display a listing of the resource.
     */
    public function show(Request $request, Project $project)
    {
        return $project
            ->prospects()
            ->with('duplicate:id,full_name')
            ->whereNotNull('duplicate_id')
            ->select('id', 'duplicate_id', 'first_name', 'last_name')
            ->get();
    }
    
    /**
     * Display a listing of the resource.
     */
    public function destroy(Request $request, Project $project)
    {
        $this->validate($request, [
            'action' => 'required|in:remove_duplicating,remove_duplicates,replace_duplicating'
        ]);

        dispatch((new HandleDuplicatedProspects(
            $project, 
            $request->input('action'), 
            $request->has('prospects') ? $request->input('prospects') : []
        ))->onConnection('sync'));
    }
}
