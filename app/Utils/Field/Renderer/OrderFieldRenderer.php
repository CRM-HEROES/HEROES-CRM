<?php

namespace App\Utils\Field\Renderer;

use App\Utils\Field\FieldRenderer;
use App\Models\Project;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderFieldRenderer implements FieldRenderer
{
    protected Project $project;
    protected Order $order;
    protected $defaultPattern = "/\\{order.(\\w+)\\}/";
    protected $metaPattern = "/\\{order.meta.(\\w+)\\}/";
    protected $defaultMatch = [];
    protected $metaMatch = [];
    
    public function __construct(Project $project, Order $order)
    {
        $this->project = $project;
        $this->order = $order;
    }

    /**
     * Compute a content
     * 
     * @param  $content
     */
    public function render($content)
    {
        if (
            !preg_match_all($this->defaultPattern, $content, $this->defaultMatch) &&
            !preg_match_all($this->metaPattern, $content, $this->metaMatch)
        ) {
            return $content;
        }

        return $this->project->fields->filter(function($field) {
            return $field->for == 'order' && (
                (!empty($this->defaultMatch) && !$field->meta && in_array($field->slug, $this->defaultMatch[1])) ||
                (!empty($this->metaMatch) && $field->meta && in_array($field->slug, $this->metaMatch[1]))
            );
        })
        ->concat([(object) ['slug' => 'number', 'type' => 'number', 'meta' => false]])
        ->reduce(function ($carry, $field) {
            return $field->meta ?
                Str::replace(
                    "{order.meta.{$field->slug}}", 
                    isset($this->order->meta[$field->slug]) ? (
                        $field->type == 'date' ? 
                            \Carbon\Carbon::createFromFormat('Y-m-d', $this->order->meta[$field->slug])->format('d/m/Y') : 
                            $this->order->meta[$field->slug]
                        ) : 
                        '', 
                    $carry
                )
                : Str::replace(
                    "{order.{$field->slug}}", 
                    $this->order->{$field->slug}, 
                    $carry
                );
        }, $content);
    }
}