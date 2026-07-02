<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use App\Models\Project;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project->smsTemplates;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectSmsTemplateAdd', $project), 404);

        $this->validate($request, [
            'name' => 'required',
            'body' => 'required'
        ]);

        return $project
            ->smsTemplates()
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
    public function show(Project $project, SmsTemplate $smsTemplate)
    {
        abort_unless($project->id == $smsTemplate->project_id, 404);

        return $smsTemplate;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, SmsTemplate $smsTemplate)
    {
        abort_unless(auth()->user()->can('projectSmsTemplateUpdate', $project), 404);
        abort_unless($project->id == $smsTemplate->project_id, 404);

        $smsTemplate->update($request->only(
            'name',
            'body',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, SmsTemplate $smsTemplate)
    {
        abort_unless(auth()->user()->can('projectSmsTemplateDelete', $project), 404);
        abort_unless($project->id == $smsTemplate->project_id, 404);

        $smsTemplate->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
