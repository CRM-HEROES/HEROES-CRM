<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use Illuminate\Support\Str;

class PageNumberRenderer implements FieldRenderer
{
    protected $types = ['text', 'html', 'order-table'];

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

        return Str::replace("{page}", $page->page, $field->content);
    }
}