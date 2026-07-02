<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use App\Utils\Field\FieldRenderer as FieldFieldRenderer;
use App\Utils\Field\Renderer\OrderFieldRenderer as RendererOrderFieldRenderer;
use App\Utils\Product\ImageThumbnail as ProductImageThumbnail;
use Illuminate\Support\Str;

class OrderFieldRenderer implements FieldRenderer
{
    protected $productFields;
    protected $productPivotFields;
    protected $productPivotMetaFields;
    protected $orderFields;
    protected $types = ['text', 'html', 'order-table'];
    protected Order $order;
    protected $productPattern = "/\\{product\\[\\d+\\].\\w+\\}/";
    protected FieldFieldRenderer $fieldRenderer; 
    
    public function __construct(Project $project, Order $order)
    {
        $this->order = $order;
        $this->productFields = collect(['currency', 'description', 'name', 'price', 'tax', 'tax_percent', 'tax_value', 'price_including_tax', 'price_excluding_tax', 'price_value']);
        $this->productPivotFields = collect(['currency', 'price', 'tax', 'tax_percent', 'tax_value', 'price_including_tax', 'price_excluding_tax', 'price_value']);
        $this->productPivotMetaFields = $this->order->prospect->project->fields()->where('for', 'order-product')->get()->pluck('slug');
        $this->orderFields = collect(['name', 'description', 'currency', 'total_excluding_tax', 'total_including_tax', 'tax_value']);
        $this->fieldRenderer = new RendererOrderFieldRenderer($project, $order);
    }

    /**
     * Render a document field
     * 
     * @param  field
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if (!in_array($field->type, $this->types)) {
            return $field->content;
        }

        $content = $this->getOrderField($field, $page);

        if (preg_match($this->productPattern, $content)) {
            
            foreach ($this->order->products as $i => $product) {
                $content = $this->getProductField($content, $i, $product);
            }

            $content = preg_replace($this->productPattern, "", $content);
        }

        return $content;
    }

    /**
     * Render a document field
     * 
     * @param  field
     */
    public function getOrderField($field, $page)
    {
        $content = $this
            ->orderFields
            ->reduce(function ($carry, $field) {
                return Str::replace(
                    "{order.{$field}}", 
                    is_numeric($this->order->{$field}) ? round($this->order->{$field}, 2) : $this->order->{$field},
                    $carry
                );
            }, $field->content);

        return $this->fieldRenderer->render($content, $page);
    }

    /**
     * Get product image
     */
    protected function getProductImage($product)
    {
        $image = $product->images()->first();
        if (!$image) {
            return "";
        }

        $thumbnail = (new ProductImageThumbnail($image, 200))->generate();
        if (!$thumbnail) {
            return "";
        }

        $thumbnail = base64_encode($thumbnail);
        return "<img src=\"data:image/png;base64, $thumbnail\" style=\"width: 100%\" />";
    }

    /**
     */
    public function getProductField($content, $index, $product)
    {
        $content = $this->productFields
            ->reduce(function ($carry, $field) use($index, $product) {
                return Str::replace(
                    "{product[{$index}].{$field}}", 
                    $product->{$field}, 
                    $carry
                );
            }, $content);

        $content = $this->productPivotFields
            ->reduce(function ($carry, $field) use($index, $product) {
                return Str::replace(
                    "{product[{$index}].pivot.{$field}}", 
                    $product->pivot->{$field}, 
                    $carry
                );
            }, $content);

        $content = $this->productPivotMetaFields
            ->reduce(function ($carry, $field) use($index, $product) {
                return Str::replace(
                    "{product[{$index}].pivot.meta.{$field}}", 
                    Str::replace("\n", "<br>", $product->pivot->meta && isset($product->pivot->meta[$field]) ? $product->pivot->meta[$field] : ''),
                    $carry
                );
            }, $content);

        // Product image
        if (Str::contains($content, "{product[{$index}].image}")) {
            $content = Str::replace(
                "{product[{$index}].image}", 
                $this->getProductImage($this->order->products[$index]), 
                $content
            );
        }

        return $content;
    }
}