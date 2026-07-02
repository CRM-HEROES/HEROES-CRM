<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;

class TextFieldRenderer implements FieldRenderer
{
    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if ($field->type != "text") {
            return $field->content;
        }

        return htmlspecialchars($field->content);
    }
}