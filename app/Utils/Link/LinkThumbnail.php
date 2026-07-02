<?php

namespace App\Utils\Link;

use App\Models\File;
use App\Models\Link;
use App\Utils\File\ImageThumbnail;
use App\Utils\File\PDFThumbnail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LinkThumbnail
{
    protected Link $link;
    protected $size;
    
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * Render a file thumbnail
     * 
     * @param  content
     */
    public function generate()
    {
        $ogImage = $this->ogImage();

        if (!$ogImage) {
            return null;
        }


    }

    /**
     * Get link OG image
     * 
     * @param  content
     */
    private function ogImage()
    {
        $html = file_get_contents($this->link->link);

        if ($html === false) {
            return null;
        }

        $pattern = '/<meta property="og:image" content="(.*?)"/i';
        preg_match($pattern, $html, $matches);

        if (isset($matches[1])) {
            return $matches[1]; // Return the value of og:image tag
        } else {
            return null; // No og:image tag found
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
}