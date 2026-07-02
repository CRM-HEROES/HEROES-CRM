<?php

namespace App\Observers;

use App\Models\Label;
use Illuminate\Support\Facades\DB;

class LabelObserver
{
    /**
     * Handle the Label "creating" event.
     */
    public function creating(Label $label): void
    {
        $label->order = $this->getOrder($label);
    }

    /**
     * Handle the Label "updating" event.
     */
    public function updating(Label $label): void
    {
        $this->updateOrder($label);
    }

    /**
     * Handle the Label "deleted" event.
     */
    public function deleted(Label $label): void
    {
        $this->deleteOrder($label);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Label  $label
     * @return void
     */
    protected function getOrder(Label $label)
    {
        return Label
            ::where('category_id', $label->category_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Label  $label
     * @return void
     */
    protected function updateOrder(Label $label)
    {
        if (!$label->isDirty('order')) {
            return;
        }

        $count = DB::table('labels')
            ->where('category_id', $label->category_id)
            ->count();

        $label->order = max(1, min($label->order, $count));

        $oldOrder = $label->getOriginal('order');
        $newOrder = $label->order;

        if ($oldOrder < $newOrder) {
            DB::table('labels')
                ->where('category_id', $label->category_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('labels')
                ->where('category_id', $label->category_id)
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
     * @param  \App\Models\Label  $label
     * @return void
     */
    protected function deleteOrder(Label $label)
    {
        DB::table('labels')
            ->where('category_id', $label->category_id)
            ->where('order', '>=', $label->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
