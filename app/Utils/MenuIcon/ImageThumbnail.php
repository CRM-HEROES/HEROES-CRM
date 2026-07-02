<?php

namespace App\Utils\MenuIcon;

class ImageThumbnail
{
    /**
     * Create a document page thumbnail
     * 
     * @param  content
     */
    static public function generate($file, $size = 150)
    {
        $imagick = new \Imagick($file);
        $imagick->setImageFormat('png');
        $backgroundColor = new \ImagickPixel('transparent');
        $imagick->setImageBackgroundColor($backgroundColor);
        $imagick = $imagick->flattenImages();
        $imagick->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1, 1);
        
        return $imagick;
    }
}