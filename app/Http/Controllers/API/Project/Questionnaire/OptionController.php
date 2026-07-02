<?php

namespace App\Http\Controllers\API\Project\Questionnaire;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireOption;
use App\Models\QuestionnaireSection;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Questionnaire $questionnaire, QuestionnaireSection $section, QuestionnaireQuestion $question)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);
        abort_unless($section->id == $question->section_id, 404);

        return $question
            ->options()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Questionnaire $questionnaire, QuestionnaireSection $section, QuestionnaireQuestion $question)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);
        abort_unless($section->id == $question->section_id, 404);
        abort_unless(in_array($question->type, ['radio', 'checkbox']), 404, "Associated question must have radio or checkbox as type.");

        return $question
            ->options()
            ->create(array_merge($request->only(
                'name',
                'redirection_id'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Questionnaire $questionnaire, QuestionnaireSection $section, QuestionnaireQuestion $question, QuestionnaireOption $option)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);
        abort_unless($section->id == $question->section_id, 404);
        abort_unless($question->id == $option->question_id, 404);

        return $option;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Questionnaire $questionnaire, QuestionnaireSection $section, QuestionnaireQuestion $question, QuestionnaireOption $option)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);
        abort_unless($section->id == $question->section_id, 404);
        abort_unless($question->id == $option->question_id, 404);

        $option->update($request->only(
            'name',
            'redirection_id',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Questionnaire $questionnaire, QuestionnaireSection $section, QuestionnaireQuestion $question, QuestionnaireOption $option)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);
        abort_unless($questionnaire->id == $section->questionnaire_id, 404);
        abort_unless($section->id == $question->section_id, 404);
        abort_unless($question->id == $option->question_id, 404);

        $option->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
