<?php

namespace App\Observers;

use App\Models\SmsTemplate;
use Illuminate\Support\Facades\DB;

class SmsTemplateObserver
{
    /**
     * Handle the SmsTemplate "creating" event.
     */
    public function creating(SmsTemplate $smsTemplate): void
    {
        $smsTemplate->order = $this->getOrder($smsTemplate);
    }

    /**
     * Handle the SmsTemplate "updating" event.
     */
    public function updating(SmsTemplate $smsTemplate): void
    {
        $this->updateOrder($smsTemplate);
    }

    /**
     * Handle the SmsTemplate "deleted" event.
     */
    public function deleted(SmsTemplate $smsTemplate): void
    {
        $this->deleteOrder($smsTemplate);
    }

    /**
     * Get order
     *
     * @param  \App\Models\SmsTemplate  $smsTemplate
     * @return void
     */
    protected function getOrder(SmsTemplate $smsTemplate)
    {
        return SmsTemplate
            ::where('project_id', $smsTemplate->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\SmsTemplate  $smsTemplate
     * @return void
     */
    protected function updateOrder(SmsTemplate $smsTemplate)
    {
        if (!$smsTemplate->isDirty('order')) {
            return;
        }

        $count = DB::table('sms_templates')
            ->where('project_id', $smsTemplate->project_id)
            ->count();

        $smsTemplate->order = max(1, min($smsTemplate->order, $count));

        $oldOrder = $smsTemplate->getOriginal('order');
        $newOrder = $smsTemplate->order;

        if ($oldOrder < $newOrder) {
            DB::table('sms_templates')
                ->where('project_id', $smsTemplate->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('sms_templates')
                ->where('project_id', $smsTemplate->project_id)
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
     * @param  \App\Models\SmsTemplate  $smsTemplate
     * @return void
     */
    protected function deleteOrder(SmsTemplate $smsTemplate)
    {
        DB::table('sms_templates')
            ->where('project_id', $smsTemplate->project_id)
            ->where('order', '>=', $smsTemplate->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
