<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer as DocumentFieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Models\Prospect;
use App\Utils\Field\FieldRenderer as FieldRenderer;
use App\Utils\Field\Renderer\ProspectFieldRenderer as RendererProspectFieldRenderer;

class ProspectFieldRenderer implements DocumentFieldRenderer
{
    protected $types = ['text', 'html', 'qrcode', 'order-table'];
    protected FieldRenderer $fieldRenderer; 
    
    public function __construct(Project $project, Prospect $prospect)
    {
        $this->fieldRenderer = new RendererProspectFieldRenderer($project, $prospect);
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

        return $this->fieldRenderer->render($field->content);
    }
}