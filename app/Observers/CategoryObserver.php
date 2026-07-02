<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     */
    public function creating(Category $category): void
    {
        $category->order = $this->getOrder($category);
    }

    /**
     * Handle the Calendar "updating" event.
     */
    public function updating(Category $category): void
    {
        $this->updateOrder($category);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->deleteOrder($category);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    protected function getOrder(Category $category)
    {
        return Category
            ::where('project_id', $category->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Calendar  $category
     * @return void
     */
    protected function updateOrder(Category $category)
    {
        if (!$category->isDirty('order')) {
            return;
        }

        $count = DB::table('categories')
            ->where('project_id', $category->project_id)
            ->count();

        $category->order = max(1, min($category->order, $count));

        $oldOrder = $category->getOriginal('order');
        $newOrder = $category->order;

        if ($oldOrder < $newOrder) {
            DB::table('categories')
                ->where('project_id', $category->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('categories')
                ->where('project_id', $category->project_id)
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
     * @param  \App\Models\Category  $category
     * @return void
     */
    protected function deleteOrder(Category $category)
    {
        DB::table('categories')
            ->where('project_id', $category->project_id)
            ->where('order', '>=', $category->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
