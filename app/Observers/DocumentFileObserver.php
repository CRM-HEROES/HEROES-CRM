<?php

namespace App\Observers;

use App\Models\DocumentFile;
use App\Models\DocumentPage;
use App\Utils\Document\DocumentFilePageThumbnail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class DocumentFileObserver
{
    /**
     * Handle the DocumentFile "creating" event.
     */
    public function creating(DocumentFile $documentFile): void
    {
        $documentFile->pages_count = $this->countPages($documentFile);
    }

    /**
     * Handle the DocumentFile "created" event.
     */
    public function created(DocumentFile $documentFile): void
    {
        $this->addPages($documentFile, request()->input('page', null));
    }

    /**
     * Handle the DocumentFile "updating" event.
     */
    public function updating(DocumentFile $documentFile): void
    {
        $this->deleteThumbnailFiles($documentFile);
        $this->unassociateExtraPages($documentFile);
    }

    /**
     * Handle the DocumentFile "updated" event.
     */
    public function updated(DocumentFile $documentFile): void
    {
        $this->addExtraPages($documentFile);
    }

    /**
     * Handle the DocumentFile "deleting" event.
     */
    public function deleting(DocumentFile $documentFile): void
    {
        $this->deletePages($documentFile);
        $this->deleteFile($documentFile);
    }

    /**
     * Add all the file page to its document
     */
    public function countPages(DocumentFile $documentFile)
    {
        $fpdi = new FPDI;
        $sourceFile = Storage::disk('documents')->path($documentFile->path);
        
        try {
            return $fpdi->setSourceFile($sourceFile);
        } catch (\Exception $e) {
            $newFilePath = $documentFile->document->project->slug . "/" . Str::random(30) . '.' . pathinfo($documentFile->path)['extension'];
            $newFile = Storage::disk('documents')->path($newFilePath);
            $command = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o " . escapeshellarg($newFile) . " " . escapeshellarg($sourceFile);
            exec($command);

            Storage::disk('documents')->delete($documentFile->path);
            $documentFile->path = $newFilePath;
            // DB::table('document_files')->where('id', $documentFile->id)->limit(1)->update(['path' => $newFilePath]);
            // $documentFile->refresh();

            /*\App\Utils\ProjectLog::error(
                $documentFile->document->project, 
                trans('document.error.version')
            );*/
            
            return $fpdi->setSourceFile($newFile);
        }
    }

    /**
     * Add all the file page to its document
     */
    public function addPages(DocumentFile $documentFile, $page = null)
    {
        $document = $documentFile->document;
        $page = $page ? $page : (optional($document->pages()->orderBy('page', 'desc')->first())->page ?: 0) + 1;

        $fpdi = new FPDI;
        
        try {
            $count = $fpdi->setSourceFile(Storage::disk('documents')->path($documentFile->path));

            for ($i = 1; $i <= $count; $i++) {
                $template = $fpdi->importPage($i);
                $size     = $fpdi->getTemplateSize($template);

                $document->pages()->updateOrCreate([
                    'page' => $page,
                ], [
                    'file_id' => $documentFile->id,
                    'file_page' => $i,
                    'width' => $size['width'] . "mm",
                    'height' => $size['height'] . "mm",
                    'orientation' => $size['orientation'],
                ]);
        
                ++$page;
            }
        } catch (\Exception $e) {
            \App\Utils\ProjectLog::error(
                $documentFile->document->project, 
                trans('document.error.version')
            );
        }
    }

    /**
     * Delete thumbnail files
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    protected function deleteThumbnailFiles(DocumentFile $documentFile)
    {
        if (!$documentFile->isDirty('path')) {
            return;
        }

        foreach ($documentFile->pages as $documentPage) {
            (new DocumentFilePageThumbnail($documentFile, $documentPage->file_page))->delete();
        }
    }

    /**
     * Delete all the file page from its document
     */
    public function deletePages(DocumentFile $documentFile): void
    {
        foreach ($documentFile->pages as $page) {
            $page->delete();
        }
    }
    
    /**
     * Delete file
     *
     * @param  \App\Models\DocumentFile  $file
     * @return void
     */
    protected function deleteFile(DocumentFile $documentFile)
    {
        if (!Storage::disk('documents')->exists($documentFile->path)) {
            return;
        }

        Storage::disk('documents')->delete($documentFile->path);
    }
    
    /**
     * Unassociate Extra Pages
     *
     * @param  \App\Models\DocumentFile  $file
     * @return void
     */
    protected function unassociateExtraPages(DocumentFile $documentFile)
    {
        if (!$documentFile->isDirty('path')) {
            return;
        }

        $fpdi = new FPDI;
        
        try {
            $count = $fpdi->setSourceFile(Storage::disk('documents')->path($documentFile->path));

            $pages = $documentFile->pages()
                ->where('file_id', $documentFile->id)
                ->where('file_page', '>', $count)
                ->get();

            foreach ($pages as $page) {
                $page->update([
                    'file_id' => null,
                    'file_page' => 0
                ]);
            }
        } catch (\Exception $e) {
            \App\Utils\ProjectLog::error(
                $documentFile->document->project, 
                trans('document.error.version')
            );
        }
    }
    
    /**
     * Unassociate Extra Pages
     *
     * @param  \App\Models\DocumentFile  $file
     * @return void
     */
    protected function addExtraPages(DocumentFile $documentFile)
    {
        if (!$documentFile->isDirty('path')) {
            return;
        }

        $fpdi = new FPDI;
        
        try {
            $oldCount = $fpdi->setSourceFile(Storage::disk('documents')->path($documentFile->getOriginal('path')));

            $page = DocumentPage::where([
                'file_id' => $documentFile->id,
                'file_page' => $oldCount
            ])->first();

            if (!$page) {
                return;
            }
        
            $newCount = $fpdi->setSourceFile(Storage::disk('documents')->path($documentFile->path));
            $extraPagesCount = $newCount - $oldCount;

            for ($i = 1; $i <= $extraPagesCount; ++$i) {
                $filePage = $oldCount + $i;
                $template = $fpdi->importPage($filePage);
                $size     = $fpdi->getTemplateSize($template);

                $documentFile->document->pages()->create([
                    'file_id' => $documentFile->id,
                    'page' => $page->page + $i,
                    'file_page' => $filePage,
                    'width' => $size['width'] . "mm",
                    'height' => $size['height'] . "mm",
                    'orientation' => $size['orientation'],
                ]);
            }
        } catch (\Exception $e) {
            \App\Utils\ProjectLog::error(
                $documentFile->document->project, 
                trans('document.error.version')
            );
        }
    }
}
