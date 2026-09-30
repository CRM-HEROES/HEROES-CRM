<?php

namespace App\Utils\Document;

use App\Models\DocumentFile;
use App\Utils\File\PDFThumbnail;
use App\Utils\ProjectLog;
use Illuminate\Support\Facades\Storage;

class DocumentFilePageThumbnail
{
    protected DocumentFile $file;
    protected $page;
    protected $size;
    
    public function __construct(DocumentFile $file, $page, $size = 150)
    {
        $this->file = $file;
        $this->page = $page;
        $this->size = $size;
    }

    /**
     * Create a document page thumbnail
     * 
     * @param  content
     */
    public function generate()
    {
        $disk = Storage::disk('documents');

        // Path to thumbnail image
        $pathinfo = pathinfo($this->file->path);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . '_' . $this->page . "_{$this->size}.thumb.jpg";

        // Create thumbnail if not exists
        if (!$disk->exists($thumbnail)) {
            try {
                $imagick = PDFThumbnail::generate($disk->path($this->file->path), $this->page, $this->size);
                $imagick->writeImage($disk->path($thumbnail));
            } catch (\Exception $e) {
                ProjectLog::error($this->file->document->project, $e->getMessage());
                throw $e;
            }
        }
        
        return $disk->get($thumbnail);
    }

    /**
     * Delete document page thumbnail
     * 
     * @param  content
     */
    public function delete()
    {
        $disk = Storage::disk('documents');

        // Path to thumbnail image
        $pathinfo = pathinfo($this->file->path);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . '_' . $this->page . "_{$this->size}.thumb.jpg";

        if (!$disk->exists($thumbnail)) {
            return false;
        }

        $disk->delete($thumbnail);

        return true;
    }
}