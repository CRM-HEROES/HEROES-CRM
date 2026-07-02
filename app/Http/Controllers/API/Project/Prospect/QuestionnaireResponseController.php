<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\ProspectQuestionnaireResponse;
use App\Models\QuestionnaireOption;
use App\Models\QuestionnaireQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionnaireResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show(Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        abort_unless($project->id == $question->section->questionnaire->project_id, 404);

        $responses = $prospect->responses()->where('question_id', $question->id)->get();

        if ($question->type == 'image' || $question->type == 'file') {
            abort_unless(count($responses) > 0, 404);
            
            $response = $responses->first();
            $disk = Storage::disk('questionnaires');
            
            abort_unless($disk->exists($response->response), 404);

            return response($disk->get($response->response))
                ->header('Content-Type', $disk->mimeType($response->response));
        }

        return $responses;
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        abort_unless($project->id == $question->section->questionnaire->project_id, 404);

        switch ($question->type) {
            case 'checkbox':
                return $this->updateCheckboxQuestion($request, $project, $prospect, $question);
            case 'radio':
                return $this->updateRadioQuestion($request, $project, $prospect, $question);
            case 'text':
            case 'textarea':
            case 'date':
                return $this->updateTextQuestion($request, $project, $prospect, $question);
            case 'image':
            case 'file':
                return $this->updateFileQuestion($request, $project, $prospect, $question);
            default:
                return response('Unknown question type.', 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        abort_unless($project->id == $question->section->questionnaire->project_id, 404);

        $responses = ProspectQuestionnaireResponse::where([
            'prospect_id' => $prospect->id,
            'question_id' => $question->id,
        ])->get();

        foreach ($responses as $response) {
            $response->delete();
        }

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Update checkbox question response
     */
    protected function updateCheckboxQuestion(Request $request, Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        ProspectQuestionnaireResponse
            ::where([
                'prospect_id' => $prospect->id,
                'question_id' => $question->id,
            ])
            ->delete();

        $options = QuestionnaireOption
            ::whereIn('id', $request->input('response', []))
            ->whereHas('question', function($query) use($question) {
                $query->where('id', $question->id);
            })
            ->get();

        foreach ($options as $option) {
            ProspectQuestionnaireResponse::create([
                'prospect_id' => $prospect->id,
                'question_id' => $question->id,
                'option_id' => $option->id,
                'creator_id' => auth()->id(),
                'response' => $option->name
            ]);
        }

        $this->updateQuestionnaireCurrentQuestion($prospect, $question);
        
        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Update radio question response
     */
    protected function updateRadioQuestion(Request $request, Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        $option = QuestionnaireOption
            ::where('id', $request->input('response', null))
            ->whereHas('question', function($query) use($question) {
                $query->where('id', $question->id);
            })
            ->first();

        if (!$option) {
            return response('Unknown option.', 400);
        }

        $response = ProspectQuestionnaireResponse::updateOrCreate([
            'prospect_id' => $prospect->id,
            'question_id' => $question->id
        ], [
            'option_id' => $option->id,
            'creator_id' => auth()->id(),
            'response' => $option->name
        ]);

        $this->updateQuestionnaireCurrentQuestion($prospect, $question);
        
        return $response;
    }

    /**
     * Update text question response
     */
    protected function updateTextQuestion(Request $request, Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        if (!$request->has('response')) {
            return response('Unknown response.', 400);
        }

        $response = ProspectQuestionnaireResponse::updateOrCreate([
            'prospect_id' => $prospect->id,
            'question_id' => $question->id
        ], [
            'creator_id' => auth()->id(),
            'response' => $request->input('response')
        ]);

        $this->updateQuestionnaireCurrentQuestion($prospect, $question);

        return $response;
    }

    /**
     * Update text question response
     */
    protected function updateFileQuestion(Request $request, Project $project, Prospect $prospect, QuestionnaireQuestion $question)
    {
        if (!$request->hasFile('response')) {
            return response('Unknown response file.', 400);
        }

        // Store file
        $response = ProspectQuestionnaireResponse::updateOrCreate(
            [
                'prospect_id' => $prospect->id,
                'question_id' => $question->id
            ], array_merge(
                [
                    'creator_id' => auth()->id(),
                ],
                $this->storeFile($request, $project)
            )
        );

        $this->updateQuestionnaireCurrentQuestion($prospect, $question);

        return $response;
    }

    /**
     * Update prospect questionnaire current question
     */
    protected function updateQuestionnaireCurrentQuestion(Prospect $prospect, QuestionnaireQuestion $question)
    {
        $prospect
            ->questionnaires()
            ->withPivot('question_id', 'updated_at')
            ->syncWithoutDetaching([$question->section->questionnaire_id => [
                'question_id' => $question->id,
                'updated_at' => \Carbon\Carbon::now()
            ]]);
    }

    /**
     * Store questionnaire response file
     */
    protected function storeFile(Request $request, Project $project)
    {
        $file = $request->file('response');
        $name = Str::random(30) . '.' . pathinfo($file->getClientOriginalName())['extension'];

        return [
            'response' => $file->storeAs($project->slug, $name, 'questionnaires'),
            'size' => $file->getSize()
        ];
    }
}
