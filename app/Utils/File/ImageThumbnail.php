<?php

namespace App\Utils\File;

class ImageThumbnail
{
    /**
     * Create a document page thumbnail
     * 
     * @param  content
     */
    static public function generate($file, $size = 150, $format = 'jpg')
    {
        $imagick = new \Imagick($file);
        if ($format) {
            $imagick->setImageFormat($format);
        }
        $backgroundColor = new \ImagickPixel('white');
        $imagick->setImageBackgroundColor($backgroundColor);
        $imagick = $imagick->flattenImages();
        $imagick->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1, 1);
        
        return $imagick;
    }
}