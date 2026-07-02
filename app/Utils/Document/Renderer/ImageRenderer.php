<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;

class ImageRenderer implements FieldRenderer
{
    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if ($field->type != "image") {
            return $field->content;
        }

        return "<img style=\"width: 100%;\" src=\"{$field->content}\" />";
    }
}