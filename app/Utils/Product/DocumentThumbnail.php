<?php

namespace App\Utils\Product;

use App\Models\ProductDocument;
use App\Utils\File\PDFThumbnail;
use Illuminate\Support\Facades\Storage;

class DocumentThumbnail
{
    protected Productdocument $document;
    protected $size;
    
    public function __construct(ProductDocument $document, $size = 150)
    {
        $this->document = $document;
        $this->size = $size;
    }

    /**
     * Render a product document thumbnail
     * 
     * @param  content
     */
    public function generate()
    {
        $disk = Storage::disk('products');
        $pathinfo = pathinfo($this->document->path);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . "_{$this->size}.thumb.jpg";
        
        if ($disk->exists($thumbnail)) {
            return $disk->get($thumbnail);
        }

        try {
            $imagick = PDFThumbnail::generate($disk->path($this->document->path), 0);
            $imagick->writeDocument($disk->path($thumbnail));

            return $disk->get($thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }
}