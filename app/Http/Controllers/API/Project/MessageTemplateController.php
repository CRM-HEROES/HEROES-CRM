<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->messageTemplates;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectMessage-templateAdd', $project), 404);

        $this->validate($request, [
            'name' => 'required',
            'body' => 'required'
        ]);

        return $project
            ->messageTemplates()
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
    public function show(Project $project, MessageTemplate $messageTemplate)
    {
        abort_unless($project->id == $messageTemplate->project_id, 404);

        return $messageTemplate;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, MessageTemplate $messageTemplate)
    {
        abort_unless(auth()->user()->can('projectMessage-templateUpdate', $project), 404);
        abort_unless($project->id == $messageTemplate->project_id, 404);

        $messageTemplate->update($request->only(
            'name',
            'body',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, MessageTemplate $messageTemplate)
    {
        abort_unless(auth()->user()->can('projectMessage-templateDelete', $project), 404);
        abort_unless($project->id == $messageTemplate->project_id, 404);

        $messageTemplate->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
