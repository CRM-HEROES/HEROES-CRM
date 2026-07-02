<?php

namespace App\Observers;

use App\Models\OrderAction;
use Illuminate\Support\Facades\DB;

class OrderActionObserver
{
    /**
     * Handle the OrderAction "creating" event.
     */
    public function creating(OrderAction $orderAction): void
    {
        $orderAction->order = $this->getOrder($orderAction);
    }

    /**
     * Handle the OrderAction "updating" event.
     */
    public function updating(OrderAction $orderAction): void
    {
        $this->updateOrder($orderAction);
    }

    /**
     * Handle the OrderAction "deleted" event.
     */
    public function deleted(OrderAction $orderAction): void
    {
        $this->deleteOrder($orderAction);
    }

    /**
     * Get order
     *
     * @param  \App\Models\OrderAction  $orderAction
     * @return void
     */
    protected function getOrder(OrderAction $orderAction)
    {
        return OrderAction
            ::where('project_id', $orderAction->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\OrderAction  $orderAction
     * @return void
     */
    protected function updateOrder(OrderAction $orderAction)
    {
        if (!$orderAction->isDirty('order')) {
            return;
        }

        $count = DB::table('order_actions')
            ->where('project_id', $orderAction->project_id)
            ->count();

        $orderAction->order = max(1, min($orderAction->order, $count));

        $oldOrder = $orderAction->getOriginal('order');
        $newOrder = $orderAction->order;

        if ($oldOrder < $newOrder) {
            DB::table('order_actions')
                ->where('project_id', $orderAction->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('order_actions')
                ->where('project_id', $orderAction->project_id)
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
     * @param  \App\Models\OrderAction  $orderAction
     * @return void
     */
    protected function deleteOrder(OrderAction $orderAction)
    {
        DB::table('order_actions')
            ->where('project_id', $orderAction->project_id)
            ->where('order', '>=', $orderAction->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
