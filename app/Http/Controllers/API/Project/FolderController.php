<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\FolderRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Project;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, FolderRequestFilters $folderRequestFilters)
    {
        return $project
            ->folders()
            ->orderBy('relevance', 'desc')
            ->orderBy('name')
            ->filter($folderRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectFolderAdd', $project), 404);
        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->folders()
            ->create(array_merge($request->only(
                'name',
                'type',
                'private',
                'color',
                'bgcolor',
                'for'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Folder $folder)
    {
        abort_unless($project->id == $folder->project_id, 404);

        return $folder;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Folder $folder)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        abort_unless(auth()->user()->can('projectFolderUpdate', $project), 404);

        $folder->update($request->only(
            'name',
            'type',
            'private',
            'color',
            'bgcolor',
            'for',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Folder $folder)
    {
        // Folder associated to the current project
        abort_unless($project->id == $folder->project_id, 404);
        abort_unless(auth()->user()->can('projectFolderDelete', $project), 404);

        $folder->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
