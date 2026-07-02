<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Order;
use App\Utils\Product\ImageThumbnail as ProductImageThumbnail;
use Illuminate\Support\Str;

class OrderTableRenderer implements FieldRenderer
{
    protected $productFields;
    protected $productPivotFields;
    protected $productPivotMetaFields;
    protected $orderFields;
    protected Order $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->productFields = collect(['currency', 'description', 'name', 'price', 'tax', 'price_value', 'price_excluding_tax', 'price_including_tax', 'tax_percent', 'tax_value']);
        $this->productPivotFields = collect(['quantity', 'currency', 'price', 'tax', 'price_value', 'tax_percent', 'price_excluding_tax', 'price_including_tax', 'tax_value', 'total_price_excluding_tax', 'total_price_including_tax', 'total_tax_value']);
        $this->productPivotMetaFields = $this->order->prospect->project->fields()->where('for', 'order-product')->get()->pluck('slug');
        $this->orderFields = collect(['name', 'description', 'currency', 'total_excluding_tax', 'total_including_tax', 'tax_value']);
    }

    /**
     * Render a document field
     * 
     * @param  field
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if ($field->type != "order-table") {
            return $field->content;
        }

        /**
         * $field->content format
         * {
         *     "columns": [
         *         // list of columns
         *     ],
         *     "summaries": [
         *         // list of summaries
         *     ],
         * }
         * 
         * column format
         * {
         *     "header": {
         *         "content": "Title of the column",
         *         "style": {
         *             // column header cell style
         *         }
         *     },
         *     "body": {
         *         // you can use in the content certaine variables such as:
         *         // {product.currency}, {product.description}, {product.name}, {product.price}, 
         *         // {product.tax}, {product.tax_percent}, {product.tax_value}, {product.price_including_tax}
         *         // {product.quantity}, {product.total}, {product.total_tax}, {product.total_including_tax}, 
         *         // {product.image}
         *         "content": "content of the column cell for each product",
         *         "style": {
         *             // column body cell style
         *         }
         *     },
         * }
         * 
         * summary format
         * {
         *     "header": {
         *         "content": "Title of the column",
         *         "style": {
         *             // column header cell style
         *         }
         *     },
         *     "body": {
         *         // you can use in the content certaine variables such as:
         *         // {order.total}, {order.tax}, {order.total_including_tax}
         *         "content": "content of the column cell for each product",
         *         "style": {
         *             // column body cell style
         *         }
         *     },
         * }
         */

        $content = $field->content;
        if (!is_array($content)) {
            $content = json_decode($content, true);
        }

        $columns = data_get($content, 'columns', []);
        $summaries = data_get($content, 'summaries', []);

        return $this->getTable($columns) . $this->getSummary($summaries);
    }

    /**
     * Get table styles
     */
    protected function getStyles(&$column, $key)
    {
        $style = data_get($column, "$key.style", []);

        return implode(';', array_map(function($value, $key) {
            return "$key: $value";
        }, $style, array_keys($style)));
    }

    /**
     * Get product image
     */
    protected function getProductImage(&$column, $product)
    {
        $image = $product->images()->first();
        if (!$image) {
            return "";
        }

        $thumbnail = (new ProductImageThumbnail($image, intval(data_get($column, "style.th.width", 50))))->generate();
        if (!$thumbnail) {
            return "";
        }

        $thumbnail = base64_encode($thumbnail);
        return "<img src=\"data:image/png;base64, $thumbnail\" style=\"width: 100%\" />";
    }

    /**
     * Get table row content
     */
    protected function getRowContent(&$column, $product, $index)
    {
        $content = $this
            ->productFields
            ->reduce(function ($carry, $field) use($product) {
                return Str::replace(
                    "{product.{$field}}", 
                    is_numeric($product->{$field}) ? round($product->{$field}, 2) : $product->{$field}, 
                    $carry
                );
            }, data_get($column, 'body.content', ""));

        // Product image
        if (Str::contains($content, "{product.image}")) {
            $content = Str::replace(
                "{product.image}", 
                $this->getProductImage($column, $product), 
                $content
            );
        }

        // Product index
        if (Str::contains($content, "{product.index}")) {
            $content = Str::replace(
                "{product.index}", 
                $index + 1, 
                $content
            );
        }

        $content = $this
            ->productPivotFields
            ->reduce(function ($carry, $field) use($product) {
                return Str::replace(
                    "{product.pivot.{$field}}", 
                    is_numeric($product->pivot->{$field}) ? round($product->pivot->{$field}, 2) : Str::replace("\n", "<br>", $product->pivot->{$field}),
                    $carry
                );
            }, $content);
        
        $content = $this
            ->productPivotMetaFields
            ->reduce(function ($carry, $field) use($product) {
                return Str::replace(
                    "{product.pivot.meta.{$field}}", 
                    Str::replace("\n", "<br>", $product->pivot->meta && isset($product->pivot->meta[$field]) ? $product->pivot->meta[$field] : ''),
                    $carry
                );
            }, $content);
        
        return $content;
    }

    /**
     * Get table header cell
     */
    protected function getHeaderCell(&$column)
    {
        $content = data_get($column, 'header.content', "");
        $styles = $this->getStyles($column, 'header');

        return "<th style=\"{$styles}\">{$content}</th>";
    }

    /**
     * Get table body cell
     */
    protected function getBodyCell(&$column, $product, $index)
    {
        $content = $this->getRowContent($column, $product, $index);
        $styles = $this->getStyles($column, 'body');

        return "<td style=\"{$styles}\">{$content}</td>";
    }

    /**
     * Get table body row
     */
    protected function getBodyRow(&$columns, &$product, $index)
    {
        return 
            "<tr>" . 
                array_reduce($columns, function($carry, $column) use($product, $index) {
                    return $carry . $this->getBodyCell($column, $product, $index);
                }, '') . 
            "</tr>";
    }

    /**
     * Get table header
     */
    protected function getHeader($columns)
    {
        return array_reduce($columns, function($carry, $column) {
            return $carry . $this->getHeaderCell($column);
        }, '');
    }

    /**
     * Get table body
     */
    protected function getBody($columns)
    {
        return $this->order
            ->products
            ->reduce(function($carry, $product, $index) use($columns) {
                return $carry . $this->getBodyRow($columns, $product, $index);
            }, '');
    }

    /**
     * Get table
     */
    protected function getTable(&$columns)
    {
        return
            "<table style=\"border-collapse: collapse; width: 100%; position: relative; top: 0; left: 0\">" .
                "<thead style=\"width: 100%;\">" .
                    $this->getHeader($columns) .
                "</thead>" .
                "<tbody>" .
                    $this->getBody($columns) .
                "</tbody>" .
            "</table>";
    }

    /**
     * Get summary header cell
     */
    protected function getSummaryHeaderCell(&$summary)
    {
        $content = data_get($summary, 'header.content', "");
        $styles = $this->getStyles($summary, 'header');

        return "<td style=\"{$styles}\">{$content}</td>";
    }

    /**
     * Get summary body cell
     */
    protected function getSummaryBodyCell(&$summary)
    {
        $styles = $this->getStyles($summary, 'body');

        $content = $this
            ->orderFields
            ->reduce(function ($carry, $field) {
                return Str::replace(
                    "{order.{$field}}", 
                    is_numeric($this->order->{$field}) ? round($this->order->{$field}, 2) : $this->order->{$field},
                    $carry
                );
            }, data_get($summary, 'body.content', ""));

        return "<td style=\"{$styles}\">{$content}</td>";
    }

    /**
     * Get summary body cell
     */
    protected function getSummaryRow(&$summary)
    {
        return 
            "<tr>" .
                $this->getSummaryHeaderCell($summary) . 
                $this->getSummaryBodyCell($summary) . 
            "</tr>";
    }

    /**
     * Get summary
     */
    protected function getSummary(&$summaries)
    {
        if (empty($summaries)) {
            return "";
        }

        return
            "<div style=\"width: 100%; position: relative\">" .
                "<table style=\"border-collapse: collapse; width: auto; position: absolute; right: 0; top: 10px; background: white\">" .
                    "<tbody>" .
                        array_reduce($summaries, function ($carry, $summary) {
                            return $carry . $this->getSummaryRow($summary);
                        }, "") .
                    "</tbody>" .
                "</table>" .
            "</div>";
    }
}