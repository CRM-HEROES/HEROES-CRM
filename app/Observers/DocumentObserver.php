<?php

namespace App\Observers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentObserver
{
    /**
     * Handle the Document "creating" event.
     */
    public function creating(Document $document): void
    {
        $document->order = $this->getOrder($document);
    }

    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        $this->setupFontsCacheDir($document);
    }

    /**
     * Handle the Document "updating" event.
     */
    public function updating(Document $document): void
    {
        $this->updateOrder($document);
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        $this->deleteOrder($document);
    }

    /**
     * Get order
     *
     * @param  \App\Models\Document  $document
     * @return void
     */
    protected function getOrder(Document $document)
    {
        return Document
            ::where('project_id', $document->project_id)
            ->count() + 1;
    }

    /**
     * 
     */
    protected function setupFontsCacheDir(Document $document)
    {
        $disk = Storage::disk('fonts');

        $folder = $document->project->slug . "/";
        if (!$disk->exists($folder)) {
            return $disk->makeDirectory($folder);
        }

        $folder = $folder . "cache/";
        if (!$disk->exists($folder)) {
            return $disk->makeDirectory($folder);
        }
        
        $folder = $folder . $document->id . "/";
        if (!$disk->exists($folder)) {
            return $disk->makeDirectory($folder);
        }
    }

    /**
     * Update order
     *
     * @param  \App\Models\Document  $document
     * @return void
     */
    protected function updateOrder(Document $document)
    {
        if (!$document->isDirty('order')) {
            return;
        }

        $count = DB::table('documents')
            ->where('project_id', $document->project_id)
            ->count();

        $document->order = max(1, min($document->order, $count));

        $oldOrder = $document->getOriginal('order');
        $newOrder = $document->order;

        if ($oldOrder < $newOrder) {
            DB::table('documents')
                ->where('project_id', $document->project_id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        } else if ($oldOrder > $newOrder) {
            DB::table('documents')
                ->where('project_id', $document->project_id)
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
     * @param  \App\Models\Document  $document
     * @return void
     */
    protected function deleteOrder(Document $document)
    {
        DB::table('documents')
            ->where('project_id', $document->project_id)
            ->where('order', '>=', $document->order)
            ->update([
                'order' => DB::raw('`order` - 1')
            ]);
    }
}
