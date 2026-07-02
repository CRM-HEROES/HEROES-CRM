<?php

namespace App\Observers;

use App\Models\QuestionnaireOption;
use Illuminate\Support\Facades\DB;

class QuestionnaireOptionObserver
{
    /**
     * Handle the QuestionnaireOption "creating" event.
     */
    public function creating(QuestionnaireOption $option): void
    {
        $option->order = $this->getOrder($option);
    }

    /**
     * Handle the QuestionnaireOption "updating" event.
     */
    public function updating(QuestionnaireOption $option): void
    {
        $this->updateOrder($option);
    }

    /**
     * Handle the QuestionnaireOption "deleted" event.
     */
    public function deleted(QuestionnaireOption $option): void
    {
        $this->deleteOrder($option);
    }

    /**
     * Get order
     *
     * @param  \App\Models\QuestionnaireOption  $option
     * @return void
     */
    protected function getOrder(QuestionnaireOption $option)
    {
        return QuestionnaireOption
            ::where('question_id', $option->question_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\QuestionnaireOption  $option
     * @return void
     */
    protected function updateOrder(QuestionnaireOption $option)
    {
        if (!$option->isDirty('order')) {
            return;
        }

        $count = DB::table('questionnaire_options')
            ->where('question_id', $option->question_id)
            ->count();

        $option->order = max(1, min($option->order, $count));

        $oldOrder = $option->getOriginal('order');
        $newOrder = $option->order;

        if ($oldOrder < $newOrder) {
            DB::table('questionnaire_options')
                ->where('question_id', $option->question_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('questionnaire_options')
                ->where('question_id', $option->question_id)
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
     * @param  \App\Models\QuestionnaireOption  $option
     * @return void
     */
    protected function deleteOrder(QuestionnaireOption $option)
    {
        DB::table('questionnaire_options')
            ->where('question_id', $option->question_id)
            ->where('order', '>=', $option->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
