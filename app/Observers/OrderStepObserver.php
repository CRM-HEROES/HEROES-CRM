<?php

namespace App\Observers;

use App\Models\OrderStep;
use Illuminate\Support\Facades\DB;

class OrderStepObserver
{
    /**
     * Handle the OrderStep "creating" event.
     */
    public function creating(OrderStep $orderStep): void
    {
        $orderStep->order = $this->getOrder($orderStep);
    }

    /**
     * Handle the OrderStep "updating" event.
     */
    public function updating(OrderStep $orderStep): void
    {
        $this->updateOrder($orderStep);
    }

    /**
     * Handle the OrderStep "deleted" event.
     */
    public function deleted(OrderStep $orderStep): void
    {
        $this->deleteOrder($orderStep);
    }

    /**
     * Get order
     *
     * @param  \App\Models\OrderStep  $orderStep
     * @return void
     */
    protected function getOrder(OrderStep $orderStep)
    {
        return OrderStep
            ::where('project_id', $orderStep->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\OrderStep  $orderStep
     * @return void
     */
    protected function updateOrder(OrderStep $orderStep)
    {
        if (!$orderStep->isDirty('order')) {
            return;
        }

        $count = DB::table('order_steps')
            ->where('project_id', $orderStep->project_id)
            ->count();

        $orderStep->order = max(1, min($orderStep->order, $count));

        $oldOrder = $orderStep->getOriginal('order');
        $newOrder = $orderStep->order;

        if ($oldOrder < $newOrder) {
            DB::table('order_steps')
                ->where('project_id', $orderStep->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('order_steps')
                ->where('project_id', $orderStep->project_id)
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
     * @param  \App\Models\OrderStep  $orderStep
     * @return void
     */
    protected function deleteOrder(OrderStep $orderStep)
    {
        DB::table('order_steps')
            ->where('project_id', $orderStep->project_id)
            ->where('order', '>=', $orderStep->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
