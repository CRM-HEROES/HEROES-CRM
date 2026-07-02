<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class MenuObserver
{
    /**
     * Handle the Menu "creating" event.
     */
    public function creating(Menu $menu): void
    {
        $menu->order = $this->getOrder($menu);
    }

    /**
     * Handle the Menu "updating" event.
     */
    public function updating(Menu $menu): void
    {
        $this->updateOrder($menu);
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {
        $this->deleteOrder($menu);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    protected function getOrder(Menu $menu)
    {
        return Menu
            ::where('project_id', $menu->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    protected function updateOrder(Menu $menu)
    {
        if (!$menu->isDirty('order')) {
            return;
        }

        $count = DB::table('menus')
            ->where('project_id', $menu->project_id)
            ->count();

        $menu->order = max(1, min($menu->order, $count));

        $oldOrder = $menu->getOriginal('order');
        $newOrder = $menu->order;

        if ($oldOrder < $newOrder) {
            DB::table('menus')
                ->where('project_id', $menu->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('menus')
                ->where('project_id', $menu->project_id)
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
     * @param  \App\Models\Menu  $menu
     * @return void
     */
    protected function deleteOrder(Menu $menu)
    {
        DB::table('menus')
            ->where('project_id', $menu->project_id)
            ->where('order', '>=', $menu->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
