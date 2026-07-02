<?php

namespace App\Utils\Message;

use App\Models\MessageAttachment;
use App\Utils\File\ImageThumbnail;
use App\Utils\File\PDFThumbnail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentThumbnail
{
    protected MessageAttachment $file;
    protected $size;
    
    public function __construct(MessageAttachment $file, $size = 150)
    {
        $this->file = $file;
        $this->size = $size;
    }

    /**
     * Render a file thumbnail
     * 
     * @param  content
     */
    public function generate()
    {
        $disk = Storage::disk('messages');
        $pathinfo = pathinfo($this->file->path);

        switch (Str::lower($pathinfo['extension'])) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
                return AttachmentThumbnail::image($disk, $this->file, $this->size);
            case 'pdf':
                return AttachmentThumbnail::pdf($disk, $this->file, $this->size);
            default:
                return null;
        }
    }

    static public function image($disk, $file, $maxSize)
    {
        $pathinfo = pathinfo($file->path);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . "_{$maxSize}.thumb.jpg";
        
        if ($disk->exists($thumbnail)) {
            return $disk->get($thumbnail);
        }

        try {
            $imagick = ImageThumbnail::generate($disk->path($file->path), $maxSize);
            $imagick->writeImage($disk->path($thumbnail));

            return $disk->get($thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }

    static public function pdf($disk, $file, $maxSize)
    {
        $pathinfo = pathinfo($file->path);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . "_{$maxSize}.thumb.jpg";
        
        if ($disk->exists($thumbnail)) {
            return $disk->get($thumbnail);
        }

        try {
            $imagick = PDFThumbnail::generate($disk->path($file->path), 1, $maxSize);
            $imagick->writeImage($disk->path($thumbnail));
            
            return $disk->get($thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }
}