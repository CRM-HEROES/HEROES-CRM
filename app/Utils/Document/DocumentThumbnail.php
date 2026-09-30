<?php

namespace App\Utils\Document;

use App\Models\Document;
use App\Models\Order;
use App\Models\Product;
use App\Models\Prospect;
use App\Utils\File\PDFThumbnail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DocumentThumbnail
{
    protected Document $document;
    protected $size;
    protected $page;
    
    public function __construct(Document $document, $page = 1, $size = 200)
    {
        $this->document = $document;
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

        if (!$disk->exists($this->document->project->slug)) {
            $disk->makeDirectory($this->document->project->slug);
        }

        // Path to thumbnail image
        $thumbnail = $this->document->project->slug . "/" . $this->document->id . "_" . $this->page . "_{$this->size}.thumb.jpg";

        // Create thumbnail if not exists
        if (
            !$disk->exists($thumbnail) || 
            $this->dateChange()->gt(\Carbon\Carbon::createFromTimestamp($disk->lastModified($thumbnail)))
        ) {
            $file = $this->generateFakeDocument();

            try {
                $imagick = PDFThumbnail::generate($file, 1, $this->size);
                $imagick->writeImage($disk->path($thumbnail));
            } catch (\Exception $e) {
                throw $e;
            } finally {
                unlink($file);
            }
        }
        
        return $disk->get($thumbnail);
    }

    protected function dateChange()
    {
        $page = $this->document
            ->pages()
            ->where('page', $this->page)
            ->first();

        if (!$page) {
            return \Carbon\Carbon::createFromTimestamp(0);
        }
            
        $date = $page->updated_at;

        $fields = $this->document
            ->fields()
            ->where(function($query) use($page) {
                $query
                    ->where('page_id', $page->id)
                    ->orWhereNull('page_id');
            })
            ->get();

        foreach ($fields as $field) {
            if ($field->updated_at && $field->updated_at->gt($date)) {
                $date = $field->updated_at;
            }
        }

        return $date;
    }

    /**
     * 
     * @param  content
     */
    protected function generateFakeDocument()
    {
        if ($this->document->for == 'order' || $this->document->for == 'invoice') {
            $order = $this->fakeOrder();
            
            $pdfRenderer = (new OrderPDFRenderer($this->document, $order, [$this->page]));
            return $pdfRenderer->render();

        } else {
            $prospect = $this->fakeProspect();

            $pdfRenderer = (new ProspectPDFRenderer($this->document, $prospect, [1]));
            return $pdfRenderer->render();
        }
    }

    /**
     * 
     */
    protected function fakeProspect() {
        $prospect = new Prospect();

        $meta = [];

        foreach ($this->document->project->fields as $field) {
            $value = $field->name . ' [Prospect]';

            if ($field->meta) {
                $meta[$field->slug] = $value;
            } else if ($field->slug == 'created_at' || $field->slug == 'updated_at' || $field->slug == 'date_of_birth') {
                $prospect->{$field->slug} = \Carbon\Carbon::now();
            } else if ($field->slug == 'latitude') {
                $prospect->{$field->slug} = 43.757854;
            } else if ($field->slug == 'longitude') {
                $prospect->{$field->slug} = 7.327459;
            } else {
                $prospect->{$field->slug} = $value;
            }
        }

        $prospect->meta = $meta;
        $prospect->project = $this->document->project;

        return $prospect;
    }

    /**
     * 
     */
    protected function fakeOrder() {
        $order = new Order();
        $order->prospect = $this->fakeProspect();
        $order->currency = "€";
        $products = [];

        for ($i = 0; $i < 2; ++$i) {
            $tax = rand(0, 20) / 100;
            $including_tax = rand(0, 1) == 1;
            $price = rand(1, 50) * 100;
            $quantity = rand(1, 4);

            $meta = [];
            $orderProductFields = $order->prospect->project
                ->fields()
                ->where('for', 'order-product')
                ->get();

            foreach ($orderProductFields as $field) {
                $meta[$field->slug] = $field->name;
            }

            $products[] = (object) ([
                'name' => "Product " . $i,
                'description' => "Product description " . $i,
                'tax' => $tax,
                'tax_percent' => $tax * 100,
                'tax_value' => $price * $tax,
                'including_tax' => $including_tax,
                'price' => $price,
                'price_value' => $price,
                'price_including_tax' => $price,
                'price_excluding_tax' => $price,
                'documents' => [],
                'currency' => "€",
                'pivot' => (object) [
                    'quantity' => $quantity,
                    'tax' => $tax,
                    'tax_percent' => $tax * 100,
                    'tax_value' => $price * $tax,
                    'including_tax' => $including_tax,
                    'price' => $price,
                    'price_value' => $price,
                    'price_including_tax' => $price,
                    'price_excluding_tax' => $price,
                    'total_tax_value' => $price * $tax * $quantity,
                    'total_price_including_tax' => $price * $quantity,
                    'total_price_excluding_tax' => $price * $quantity,
                    'currency' => "€",
                    'meta' => $meta
                ]
            ]);
        }

        $order->products = new Collection($products);

        return $order;
    }
}