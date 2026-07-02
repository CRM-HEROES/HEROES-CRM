<?php

namespace App\Observers;

use App\Models\Group;
use Illuminate\Support\Facades\DB;

class GroupObserver
{
    /**
     * Handle the Group "creating" event.
     */
    public function creating(Group $group): void
    {
        $group->order = $this->getOrder($group);
    }

    /**
     * Handle the Group "updating" event.
     */
    public function updating(Group $group): void
    {
        $this->updateOrder($group);
    }

    /**
     * Handle the Group "deleted" event.
     */
    public function deleted(Group $group): void
    {
        $this->deleteOrder($group);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    protected function getOrder(Group $group)
    {
        return Group
            ::where('project_id', $group->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Group  $group
     * @return void
     */
    protected function updateOrder(Group $group)
    {
        if (!$group->isDirty('order')) {
            return;
        }

        $count = DB::table('groups')
            ->where('project_id', $group->project_id)
            ->count();

        $group->order = max(1, min($group->order, $count));

        $oldOrder = $group->getOriginal('order');
        $newOrder = $group->order;

        if ($oldOrder < $newOrder) {
            DB::table('groups')
                ->where('project_id', $group->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('groups')
                ->where('project_id', $group->project_id)
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
     * @param  \App\Models\Group  $group
     * @return void
     */
    protected function deleteOrder(Group $group)
    {
        DB::table('groups')
            ->where('project_id', $group->project_id)
            ->where('order', '>=', $group->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
