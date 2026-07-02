<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer as DocumentFieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Utils\Field\FieldRenderer;
use App\Utils\Field\Renderer\ProjectFieldRenderer as RendererProjectFieldRenderer;

class ProjectFieldRenderer implements DocumentFieldRenderer
{
    protected $types = ['text', 'html', 'qrcode', 'order-table'];
    protected FieldRenderer $fieldRenderer; 
    
    public function __construct(Project $project)
    {
        $this->fieldRenderer = new RendererProjectFieldRenderer($project);
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

        return $this->fieldRenderer->render($field->content);
    }
}