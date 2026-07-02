<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Support\Str;

class DateRenderer implements FieldRenderer
{
    protected $types = ['text', 'html', 'order-table'];
    protected $pattern = "/\\{date.([\\-\\/\\w]+)\\}/";
    
    public function __construct()
    {
    }

    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if (!in_array($field->type, $this->types)) {
            return $field->content;
        }

        $match = [];
        if (!preg_match_all($this->pattern, $field->content, $match)) {
            return $field->content;
        }

        return array_reduce($match[1], function($carry, $format) {
            return Str::replace("{date.$format}", \Carbon\Carbon::now()->format($format), $carry);
        }, $field->content);
    }
}