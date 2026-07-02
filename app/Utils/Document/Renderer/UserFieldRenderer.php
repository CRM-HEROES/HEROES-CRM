<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer as DocumentFieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Models\User;
use App\Utils\Field\FieldRenderer;
use App\Utils\Field\Renderer\UserFieldRenderer as RendererUserFieldRenderer;

class UserFieldRenderer implements DocumentFieldRenderer
{
    protected $types = ['text', 'html', 'qrcode', 'order-table'];
    protected FieldRenderer $fieldRenderer; 
    
    public function __construct(Project $project, ?User $user)
    {
        $this->fieldRenderer = new RendererUserFieldRenderer($project, $user ? $project->users()->find($user->id) : null);
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