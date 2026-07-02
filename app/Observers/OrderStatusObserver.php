<?php

namespace App\Observers;

use App\Models\OrderStatus;
use Illuminate\Support\Facades\DB;

class OrderStatusObserver
{
    /**
     * Handle the OrderStatus "creating" event.
     */
    public function creating(OrderStatus $orderStatus): void
    {
        $orderStatus->order = $this->getOrder($orderStatus);
    }

    /**
     * Handle the OrderStatus "updating" event.
     */
    public function updating(OrderStatus $orderStatus): void
    {
        $this->updateOrder($orderStatus);
    }

    /**
     * Handle the OrderStatus "deleted" event.
     */
    public function deleted(OrderStatus $orderStatus): void
    {
        $this->deleteOrder($orderStatus);
    }

    /**
     * Get order
     *
     * @param  \App\Models\OrderStatus  $orderStatus
     * @return void
     */
    protected function getOrder(OrderStatus $orderStatus)
    {
        return OrderStatus
            ::where('project_id', $orderStatus->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\OrderStatus  $orderStatus
     * @return void
     */
    protected function updateOrder(OrderStatus $orderStatus)
    {
        if (!$orderStatus->isDirty('order')) {
            return;
        }

        $count = DB::table('order_status')
            ->where('project_id', $orderStatus->project_id)
            ->count();

        $orderStatus->order = max(1, min($orderStatus->order, $count));

        $oldOrder = $orderStatus->getOriginal('order');
        $newOrder = $orderStatus->order;

        if ($oldOrder < $newOrder) {
            DB::table('order_status')
                ->where('project_id', $orderStatus->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('order_status')
                ->where('project_id', $orderStatus->project_id)
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
     * @param  \App\Models\OrderStatus  $orderStatus
     * @return void
     */
    protected function deleteOrder(OrderStatus $orderStatus)
    {
        DB::table('order_status')
            ->where('project_id', $orderStatus->project_id)
            ->where('order', '>=', $orderStatus->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
