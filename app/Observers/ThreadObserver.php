<?php

namespace App\Observers;

use App\Models\Thread;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ThreadObserver
{
    /**
     * Handle the Thread "creating" event.
     */
    public function creating(Thread $thread): void
    {
        // Slug
        if (!$thread->slug) {
            $thread->slug = $this->getAvailableSlug($thread);
        }
        
        $thread->order = $this->getOrder($thread);
    }
    
    /**
     * Handle the Thread "created" event.
     */
    public function created(Thread $thread): void
    {
        if ($thread->user) {
            $thread->users()->syncWithoutDetaching($thread->user->id);
        }
    }
    
    /**
     * Handle the Thread "updating" event.
     */
    public function updating(Thread $thread): void
    {
        $this->updateOrder($thread);
    }

    /**
     * Handle the Thread "deleted" event.
     */
    public function deleted(Thread $thread): void
    {
        $this->deleteOrder($thread);
    }

    /**
     * Get available slug
     *
     * @param  \App\Models\Field  $field
     * @return void
     */
    protected function getAvailableSlug(Thread $thread)
    {
        $defaultSlug = Str::slug($thread->name, '_');

        for ($i = 1;; ++$i) {
            $slug = $i == 1 ? 
                $defaultSlug : 
                $defaultSlug . "_" . $i;

            // Skip if slug already belongs to another thread
            if (Thread::where('project_id', $thread->project_id)->where('slug', $slug)->first()) {
                continue;
            }

            // Available slug
            return $slug;
        }
    }

    /**
     * Get order
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function getOrder(Thread $thread)
    {
        return Thread
            ::where('project_id', $thread->project_id)
            ->count() + 1;
    }

    /**
     * Update order
     *
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function updateOrder(Thread $thread)
    {
        if (!$thread->isDirty('order')) {
            return;
        }

        $count = DB::table('threads')
            ->where('project_id', $thread->project_id)
            ->count();

        $thread->order = max(1, min($thread->order, $count));

        $oldOrder = $thread->getOriginal('order');
        $newOrder = $thread->order;

        if ($oldOrder < $newOrder) {
            DB::table('threads')
                ->where('project_id', $thread->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('threads')
                ->where('project_id', $thread->project_id)
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
     * @param  \App\Models\Thread  $thread
     * @return void
     */
    protected function deleteOrder(Thread $thread)
    {
        DB::table('threads')
            ->where('project_id', $thread->project_id)
            ->where('order', '>=', $thread->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
