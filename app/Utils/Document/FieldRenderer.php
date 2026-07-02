<?php

namespace App\Utils\Document;

use App\Models\DocumentField;
use App\Models\DocumentPage;

interface FieldRenderer
{
    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render(DocumentField $field, DocumentPage $page);
}