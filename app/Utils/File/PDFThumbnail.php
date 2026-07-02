<?php

namespace App\Utils\File;

class PDFThumbnail
{
    /**
     * Create a document page thumbnail
     * 
     * @param  content
     */
    static public function generate($file, $page, $size = 150)
    {
        $imagick = new \Imagick("{$file}[" . ($page - 1) . "]");
        $imagick->setImageFormat('jpg');
        $backgroundColor = new \ImagickPixel('white');
        $imagick->setImageBackgroundColor($backgroundColor);
        $imagick = $imagick->flattenImages();
        $imagick->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1, 1);
        
        return $imagick;
    }
}