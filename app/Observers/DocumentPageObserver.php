<?php

namespace App\Observers;

use App\Models\DocumentPage;
use App\Utils\Document\DocumentFilePageThumbnail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class DocumentPageObserver
{
    /**
     * Handle the DocumentPage "created" event.
     */
    public function creating(DocumentPage $documentPage): void
    {
        $this->defineSize($documentPage);
        $this->rearrangePagesBeforeInsertion($documentPage);
    }

    /**
     * Handle the DocumentPage "updated" event.
     */
    public function updating(DocumentPage $documentPage): void
    {
        $this->deleteUpdatedPageThumbnailFiles($documentPage);
        $this->rearrangePagesBeforeUpdate($documentPage);
    }

    /**
     * Handle the DocumentPage "deleted" event.
     */
    public function deleting(DocumentPage $documentPage): void
    {
        $this->rearrangePagesBeforeDelete($documentPage);
    }

    /**
     * Delete thumbnail files
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    protected function deleteThumbnailFiles(DocumentPage $documentPage)
    {
        (new DocumentFilePageThumbnail($documentPage->file, $documentPage->file_page))->delete();
    }
    
    /**
     * Delete thumbnail files
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    protected function deleteUpdatedPageThumbnailFiles(DocumentPage $documentPage)
    {
        if (
            !$documentPage->isDirty('file_id') &&
            !$documentPage->isDirty('file_page')
        ) {
            return;
        }

        $this->deleteThumbnailFiles($documentPage);
    }
    
    /**
     * Rearrange document page before new page created
     */
    public function defineSize(DocumentPage $documentPage): void
    {
        if (!$documentPage->file_id) {
            return;
        }

        $documentFile = $documentPage->file;

        $fpdi = new Fpdi;
        
        try {
            $fpdi->setSourceFile(Storage::disk('documents')->path($documentFile->path));

            $template = $fpdi->importPage($documentPage->file_page);
            $size     = $fpdi->getTemplateSize($template);

            $documentPage->width = $size['width'] . "mm";
            $documentPage->height = $size['height'] . "mm";

        } catch (\Exception $e) {
            /*\App\Utils\ProjectLog::error(
                $documentFile->document->project, 
                trans('document.error.version')
            );*/
        }
    }
    
    /**
     * Rearrange document page before new page created
     */
    public function rearrangePagesBeforeInsertion(DocumentPage $documentPage): void
    {
        DB::table('document_pages')
            ->where('document_id', $documentPage->document_id)
            ->where('page', '>=', $documentPage->page)
            ->update([
                'page' => DB::raw('page + 1')
            ]);
    }
    
    /**
     * Rearrange document page before page updated
     */
    public function rearrangePagesBeforeUpdate(DocumentPage $documentPage): void
    {
        if (!$documentPage->isDirty('page')) {
            return;
        }

        $oldPage = $documentPage->getOriginal('page');
        $newPage = $documentPage->page;

        if ($oldPage < $newPage) {
            DB::table('document_pages')
                ->where('document_id', $documentPage->document_id)
                ->where('page', '>', $oldPage)
                ->where('page', '<=', $newPage)
                ->update([
                    'page' => DB::raw('page - 1')
                ]);
        } else if ($oldPage > $newPage) {
            DB::table('document_pages')
                ->where('document_id', $documentPage->document_id)
                ->where('page', '>=', $newPage)
                ->where('page', '<', $oldPage)
                ->update([
                    'page' => DB::raw('page + 1')
                ]);
        }
    }
    
    /**
     * Rearrange document page before page deleted
     */
    public function rearrangePagesBeforeDelete(DocumentPage $documentPage): void
    {
        DB::table('document_pages')
            ->where('page', '>', $documentPage->page)
            ->update([
                'page' => DB::raw('page - 1')
            ]);
    }
}
