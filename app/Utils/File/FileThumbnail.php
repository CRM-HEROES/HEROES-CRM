<?php

namespace App\Utils\File;

use App\Models\File;
use App\Utils\File\ImageThumbnail;
use App\Utils\File\PDFThumbnail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileThumbnail
{
    protected $file;
    protected $size;
    
    public function __construct($file, $size = 150)
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
        $disk = Storage::disk('folders');
        $pathinfo = pathinfo($this->file->path);

        switch (Str::lower($pathinfo['extension'])) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
                return FileThumbnail::image($disk, $this->file, $this->size);
            case 'pdf':
                return FileThumbnail::pdf($disk, $this->file, $this->size);
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
            $imagick = ImageThumbnail::generate($disk->path($file->path));
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
            $imagick = PDFThumbnail::generate($disk->path($file->path), 1);
            $imagick->writeImage($disk->path($thumbnail));
            
            return $disk->get($thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }
}