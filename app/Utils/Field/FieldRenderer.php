<?php

namespace App\Utils\Field;

interface FieldRenderer
{
    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render($content);
}