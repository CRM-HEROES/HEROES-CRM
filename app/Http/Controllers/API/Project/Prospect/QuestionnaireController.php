<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\ProspectQuestionnaireResponse;
use App\Models\Questionnaire;

class QuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return $prospect->questionnaires;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Questionnaire $questionnaire)
    {
        return ProspectQuestionnaireResponse
            ::where('prospect_id', $prospect->id)
            ->whereHas('question', function($query) use($questionnaire) {
                return $query->whereHas('section', function($query) use($questionnaire) {
                    return $query->where('questionnaire_id', $questionnaire->id);
                });
            })
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Prospect $prospect, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $prospect
            ->questionnaires()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$questionnaire->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Questionnaire $questionnaire)
    {
        abort_unless($project->id == $questionnaire->project_id, 404);

        $prospect->questionnaires()->detach($questionnaire->id);

        return ['message' => trans('common.success.deleted_resource')];
    }
}
