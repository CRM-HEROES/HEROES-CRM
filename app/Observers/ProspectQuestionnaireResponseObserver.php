<?php

namespace App\Observers;

use App\Models\ProspectQuestionnaireResponse;
use App\Models\QuestionnaireQuestion;
use Illuminate\Support\Facades\Storage;

class ProspectQuestionnaireResponseObserver
{
    /**
     * Handle the ProspectQuestionnaireResponse "created" event.
     */
    public function created(ProspectQuestionnaireResponse $response): void
    {
        $this->nextQuestion($response);
    }

    /**
     * Handle the ProspectQuestionnaireResponse "updated" event.
     */
    public function updated(ProspectQuestionnaireResponse $response): void
    {
        $this->nextQuestion($response);
        $this->removeUpdatedResponseFile($response);
    }

    /**
     * Handle the ProspectQuestionnaireResponse "deleting" event.
     */
    public function deleting(ProspectQuestionnaireResponse $response): void
    {
        $this->removeDeletedResponseFile($response);
    }

    /**
     * Pass to the next question
     */
    protected function nextQuestion(ProspectQuestionnaireResponse $response): void
    {
        $question = $response->question;
        $questionnaire = $question->section->questionnaire;

        // Redirection question
        if ($response->option && $response->option->redirection_id) {
            $response->prospect->questionnaires()->syncWithoutDetaching([$questionnaire->id => [
                'question_id' => $response->option->redirection_id
            ]]);

            return;
        }

        // All questionnaire question
        $questions = QuestionnaireQuestion::
            join('questionnaire_sections', 'questionnaire_sections.id', '=', 'questionnaire_questions.section_id')
            ->whereHas('section', function($query) use($questionnaire) {
                $query->where('questionnaire_id', $questionnaire->id);
            })
            ->select('questionnaire_questions.id')
            ->orderBy('questionnaire_sections.order')
            ->orderBy('questionnaire_questions.order')
            ->get();

        // Current question index
        $index = $questions->search(function($q) use($question) {
            return $q->id == $question->id;
        });

        if ($index === false) {
            return;
        }

        // Last question
        if ($index == $questions->count() - 1) {
            return;
        }

        $response->prospect->questionnaires()->syncWithoutDetaching([$questionnaire->id => [
            'question_id' => $questions[$index + 1]->id
        ]]);
    }

    /**
     * Remove updated response file
     */
    protected function removeUpdatedResponseFile(ProspectQuestionnaireResponse $response): void
    {
        if ($response->question->type != 'file') {
            return;
        }

        if (!$response->isDirty('response')) {
            return;
        }

        $oldFile = $response->getOriginal('response');
        if (!$oldFile) {
            return;
        }

        if (!Storage::disk('questionnaires')->exists($oldFile)) {
            return;
        }

        Storage::disk('questionnaires')->delete($oldFile);
    }

    /**
     * Remove deleted response file
     */
    protected function removeDeletedResponseFile(ProspectQuestionnaireResponse $response): void
    {
        if ($response->question->type != 'file') {
            return;
        }

        if (!Storage::disk('questionnaires')->exists($response->response)) {
            return;
        }

        Storage::disk('questionnaires')->delete($response->response);
    }
}
