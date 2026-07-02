<?php

namespace App\Utils\Product;

use App\Models\ProductImage;
use App\Utils\File\ImageThumbnail as FileImageThumbnail;
use Illuminate\Support\Facades\Storage;

class ImageThumbnail
{
    protected ProductImage $image;
    protected $size;
    
    public function __construct(ProductImage $image, $size = 150)
    {
        $this->image = $image;
        $this->size = $size;
    }

    /**
     * Render a product image thumbnail
     * 
     * @param  content
     */
    public function generate()
    {
        $disk = Storage::disk('products');
        $pathinfo = pathinfo($this->image->path);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . "_{$this->size}.thumb.jpg";
        
        if ($disk->exists($thumbnail)) {
            return $disk->get($thumbnail);
        }

        try {
            $imagick = FileImageThumbnail::generate($disk->path($this->image->path));
            $imagick->writeImage($disk->path($thumbnail));

            return $disk->get($thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }
}