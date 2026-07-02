<?php

namespace App\Observers;

use App\Models\MessageTemplate;
use Illuminate\Support\Facades\DB;

class MessageTemplateObserver
{
    /**
     * Handle the MessageTemplate "creating" event.
     */
    public function creating(MessageTemplate $messageTemplate): void
    {
        $messageTemplate->order = $this->getOrder($messageTemplate);
    }

    /**
     * Handle the MessageTemplate "updating" event.
     */
    public function updating(MessageTemplate $messageTemplate): void
    {
        $this->updateOrder($messageTemplate);
    }

    /**
     * Handle the MessageTemplate "deleted" event.
     */
    public function deleted(MessageTemplate $messageTemplate): void
    {
        $this->deleteOrder($messageTemplate);
    }

    /**
     * Get order
     *
     * @param  \App\Models\MessageTemplate  $messageTemplate
     * @return void
     */
    protected function getOrder(MessageTemplate $messageTemplate)
    {
        return MessageTemplate
            ::where('project_id', $messageTemplate->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\MessageTemplate  $messageTemplate
     * @return void
     */
    protected function updateOrder(MessageTemplate $messageTemplate)
    {
        if (!$messageTemplate->isDirty('order')) {
            return;
        }

        $count = DB::table('message_templates')
            ->where('project_id', $messageTemplate->project_id)
            ->count();

        $messageTemplate->order = max(1, min($messageTemplate->order, $count));

        $oldOrder = $messageTemplate->getOriginal('order');
        $newOrder = $messageTemplate->order;

        if ($oldOrder < $newOrder) {
            DB::table('message_templates')
                ->where('project_id', $messageTemplate->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('message_templates')
                ->where('project_id', $messageTemplate->project_id)
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
     * @param  \App\Models\MessageTemplate  $messageTemplate
     * @return void
     */
    protected function deleteOrder(MessageTemplate $messageTemplate)
    {
        DB::table('message_templates')
            ->where('project_id', $messageTemplate->project_id)
            ->where('order', '>=', $messageTemplate->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
