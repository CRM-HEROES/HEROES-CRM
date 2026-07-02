<?php

namespace App\Observers;

use App\Models\Questionnaire;
use Illuminate\Support\Facades\DB;

class QuestionnaireObserver
{
    /**
     * Handle the Questionnaire "creating" event.
     */
    public function creating(Questionnaire $questionnaire): void
    {
        $questionnaire->order = $this->getOrder($questionnaire);
    }

    /**
     * Handle the Questionnaire "updating" event.
     */
    public function updating(Questionnaire $questionnaire): void
    {
        $this->updateOrder($questionnaire);
    }

    /**
     * Handle the Questionnaire "deleted" event.
     */
    public function deleted(Questionnaire $questionnaire): void
    {
        $this->deleteOrder($questionnaire);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Questionnaire  $questionnaire
     * @return void
     */
    protected function getOrder(Questionnaire $questionnaire)
    {
        return Questionnaire
            ::where('project_id', $questionnaire->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Questionnaire  $questionnaire
     * @return void
     */
    protected function updateOrder(Questionnaire $questionnaire)
    {
        if (!$questionnaire->isDirty('order')) {
            return;
        }

        $count = DB::table('questionnaires')
            ->where('project_id', $questionnaire->project_id)
            ->count();

        $questionnaire->order = max(1, min($questionnaire->order, $count));

        $oldOrder = $questionnaire->getOriginal('order');
        $newOrder = $questionnaire->order;

        if ($oldOrder < $newOrder) {
            DB::table('questionnaires')
                ->where('project_id', $questionnaire->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('questionnaires')
                ->where('project_id', $questionnaire->project_id)
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
     * @param  \App\Models\Questionnaire  $questionnaire
     * @return void
     */
    protected function deleteOrder(Questionnaire $questionnaire)
    {
        DB::table('questionnaires')
            ->where('project_id', $questionnaire->project_id)
            ->where('order', '>=', $questionnaire->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
