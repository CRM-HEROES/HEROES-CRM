<?php

namespace App\Http\Controllers\API\Project\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ImportCheckDuplicatedProspects;
use App\Jobs\ImportHandleDuplicatedProspects;
use App\Models\Import;
use App\Models\Project;
use Illuminate\Http\Request;

class DuplicateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function find(Request $request, Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);
        
        $this->validate($request, [
            'fields' => 'required',
        ]);

        dispatch((new ImportCheckDuplicatedProspects(
            $import, 
            $request->input('fields')
        ))->onConnection('sync'));
    }
    
    /**
     * Display a listing of the resource.
     */
    public function show(Request $request, Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);
        
        return $project
            ->prospects()
            ->with('duplicate:id,first_name,last_name')
            ->whereNotNull('duplicate_id')
            ->select('id', 'duplicate_id', 'first_name', 'last_name')
            ->get();
    }
    
    /**
     * Display a listing of the resource.
     */
    public function destroy(Request $request, Project $project, Import $import)
    {
        abort_unless($project->id == $import->project_id, 404);
        
        $this->validate($request, [
            'action' => 'required|in:remove_duplicating,remove_duplicates,replace_duplicating'
        ]);

        dispatch((new ImportHandleDuplicatedProspects(
            $import, 
            $request->input('action'), 
            $request->has('prospects') ? $request->input('prospects') : []
        ))->onConnection('sync'));
    }
}
