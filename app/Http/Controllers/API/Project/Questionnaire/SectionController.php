<?php

namespace App\Http\Controllers\API\Project\Questionnaire;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Questionnaire;
use App\Models\QuestionnaireSection;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        return $questionnaire
            ->sections()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        return $questionnaire
            ->sections()
            ->create(array_merge($request->only(
                'name'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Questionnaire $questionnaire, QuestionnaireSection $section)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);

        return $section;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Questionnaire $questionnaire, QuestionnaireSection $section)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);

        $section->update($request->only(
            'name',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Questionnaire $questionnaire, QuestionnaireSection $section)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);

        $section->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
