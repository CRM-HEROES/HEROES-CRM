<?php

namespace App\Observers;

use App\Models\QuestionnaireSection;
use Illuminate\Support\Facades\DB;

class QuestionnaireSectionObserver
{
    /**
     * Handle the QuestionnaireSection "creating" event.
     */
    public function creating(QuestionnaireSection $section): void
    {
        $section->order = $this->getOrder($section);
    }
    
    /**
     * Handle the QuestionnaireSection "updating" event.
     */
    public function updating(QuestionnaireSection $section): void
    {
        $this->updateOrder($section);
    }

    /**
     * Handle the QuestionnaireSection "deleted" event.
     */
    public function deleted(QuestionnaireSection $section): void
    {
        $this->deleteOrder($section);
    }

    /**
     * Get new section order in questaionnaire
     */
    protected function getOrder(QuestionnaireSection $section)
    {
        return QuestionnaireSection
            ::where('questionnaire_id', $section->questionnaire_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\QuestionnaireSection  $section
     * @return void
     */
    protected function updateOrder(QuestionnaireSection $section)
    {
        if (!$section->isDirty('order')) {
            return;
        }

        $count = DB::table('questionnaire_sections')
            ->where('questionnaire_id', $section->questionnaire_id)
            ->count();

        $section->order = max(1, min($section->order, $count));

        $oldOrder = $section->getOriginal('order');
        $newOrder = $section->order;

        if ($oldOrder < $newOrder) {
            DB::table('questionnaire_sections')
                ->where('questionnaire_id', $section->questionnaire_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('questionnaire_sections')
                ->where('questionnaire_id', $section->questionnaire_id)
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
     * @param  \App\Models\QuestionnaireSection  $section
     * @return void
     */
    protected function deleteOrder(QuestionnaireSection $section)
    {
        DB::table('questionnaire_sections')
            ->where('questionnaire_id', $section->questionnaire_id)
            ->where('order', '>=', $section->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
