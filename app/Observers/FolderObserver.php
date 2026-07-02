<?php

namespace App\Observers;

use App\Models\Folder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FolderObserver
{
    /**
     * Handle the Folder "creating" event.
     */
    public function creating(Folder $folder): void
    {
        // Slug
        if (!$folder->slug) {
            $folder->slug = $this->getAvailableSlug($folder);
        }
        
        $folder->order = $this->getOrder($folder);
    }
    
    /**
     * Handle the Folder "updating" event.
     */
    public function updating(Folder $folder): void
    {
        $this->updateOrder($folder);
    }

    /**
     * Handle the Folder "deleted" event.
     */
    public function deleted(Folder $folder): void
    {
        $this->deleteOrder($folder);
    }

    /**
     * Get available slug
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    protected function getAvailableSlug(Folder $folder)
    {
        $defaultSlug = Str::slug($folder->name, '_');

        for ($i = 1;; ++$i) {
            $slug = $i == 1 ? 
                $defaultSlug : 
                $defaultSlug . "_" . $i;

            // Skip if slug already belongs to another folder
            if (Folder::where('project_id', $folder->project_id)->where('slug', $slug)->first()) {
                continue;
            }

            // Available slug
            return $slug;
        }
    }

    /**
     * Get order
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    protected function getOrder(Folder $folder)
    {
        return Folder
            ::where('project_id', $folder->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    protected function updateOrder(Folder $folder)
    {
        if (!$folder->isDirty('order')) {
            return;
        }

        $count = DB::table('folders')
            ->where('project_id', $folder->project_id)
            ->count();

        $folder->order = max(1, min($folder->order, $count));

        $oldOrder = $folder->getOriginal('order');
        $newOrder = $folder->order;

        if ($oldOrder < $newOrder) {
            DB::table('folders')
                ->where('project_id', $folder->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('folders')
                ->where('project_id', $folder->project_id)
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
     * @param  \App\Models\Folder  $folder
     * @return void
     */
    protected function deleteOrder(Folder $folder)
    {
        DB::table('folders')
            ->where('project_id', $folder->project_id)
            ->where('order', '>=', $folder->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
