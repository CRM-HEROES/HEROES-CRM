<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\EventDescriptionTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class EventDescriptionTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->eventDescriptionTemplates;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectEvent-description-templateAdd', $project), 404);

        $this->validate($request, [
            'name' => 'required',
            'body' => 'required'
        ]);

        return $project
            ->eventDescriptionTemplates()
            ->create(array_merge($request->only(
                'name',
                'body'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, EventDescriptionTemplate $eventDescriptionTemplate)
    {
        abort_unless($project->id == $eventDescriptionTemplate->project_id, 404);

        return $eventDescriptionTemplate;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, EventDescriptionTemplate $eventDescriptionTemplate)
    {
        abort_unless(auth()->user()->can('projectEvent-description-templateUpdate', $project), 404);
        abort_unless($project->id == $eventDescriptionTemplate->project_id, 404);

        $eventDescriptionTemplate->update($request->only(
            'name',
            'body',
            'order'
        ));

        return ['eventDescription' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, EventDescriptionTemplate $eventDescriptionTemplate)
    {
        abort_unless(auth()->user()->can('projectEvent-description-templateDelete', $project), 404);
        abort_unless($project->id == $eventDescriptionTemplate->project_id, 404);

        $eventDescriptionTemplate->delete();

        return ['eventDescription' => trans('common.success.deleted_resource')];
    }
}
