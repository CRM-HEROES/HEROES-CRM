<?php

namespace App\Observers;

use App\Models\QuestionnaireQuestion;
use Illuminate\Support\Facades\DB;

class QuestionnaireQuestionObserver
{
    /**
     * Handle the QuestionnaireOption "creating" event.
     */
    public function creating(QuestionnaireQuestion $question): void
    {
        $question->order = $this->getOrder($question);
    }

    /**
     * Handle the QuestionnaireQuestion "updating" event.
     */
    public function updating(QuestionnaireQuestion $question): void
    {
        $this->removeOptions($question);
        $this->updateOrder($question);
    }

    /**
     * Handle the QuestionnaireQuestion "deleted" event.
     */
    public function deleted(QuestionnaireQuestion $question): void
    {
        $this->deleteOrder($question);
    }

    /**
     * Get order
     *
     * @param  \App\Models\QuestionnaireQuestion  $question
     * @return void
     */
    protected function getOrder(QuestionnaireQuestion $question)
    {
        return QuestionnaireQuestion
            ::where('section_id', $question->section_id)
            ->count() + 1;
    }

    /**
     * If question type has been changed to text
     * Delete question options
     */
    protected function removeOptions(QuestionnaireQuestion $question)
    {
        if (!$question->isDirty('type')) {
            return;
        }
        
        if ($question->getOriginal('type') != 'radio' && $question->getOriginal('type') != 'checkbox') {
            return;
        }
        
        if ($question->type == 'radio' || $question->type == 'checkbox') {
            return;
        }

        $question->options()->delete();
    }

    /**
     * Update order
     *
     * @param  \App\Models\QuestionnaireQuestion  $question
     * @return void
     */
    protected function updateOrder(QuestionnaireQuestion $question)
    {
        if (!$question->isDirty('order')) {
            return;
        }

        $count = DB::table('questionnaire_questions')
            ->where('section_id', $question->section_id)
            ->count();

        $question->order = max(1, min($question->order, $count));

        $oldOrder = $question->getOriginal('order');
        $newOrder = $question->order;

        if ($oldOrder < $newOrder) {
            DB::table('questionnaire_questions')
                ->where('section_id', $question->section_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('questionnaire_questions')
                ->where('section_id', $question->section_id)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->update([
                    'order' => DB::raw('`order` + 1')
                ]);
        }
    }

    /**
     * Delete order
     *
     * @param  \App\Models\QuestionnaireQuestion  $question
     * @return void
     */
    protected function deleteOrder(QuestionnaireQuestion $question)
    {
        DB::table('questionnaire_questions')
            ->where('section_id', $question->section_id)
            ->where('order', '>=', $question->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
