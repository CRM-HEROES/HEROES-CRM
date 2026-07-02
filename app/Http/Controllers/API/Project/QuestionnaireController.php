<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->questionnaires()
            ->orderBy('relevance', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->questionnaires()
            ->create(array_merge($request->only(
                'name'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $questionnaire->load('sections.questions.options');

        return $questionnaire;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $questionnaire->update($request->only(
            'name',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $questionnaire->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
