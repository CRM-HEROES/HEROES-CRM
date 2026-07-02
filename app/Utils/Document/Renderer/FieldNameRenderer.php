<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use Illuminate\Support\Str;

class FieldNameRenderer implements FieldRenderer
{
    protected $types = ['text', 'html', 'order-table'];
    protected Project $project;
    
    public function __construct(Project $project)
    {
        $this->project = $project;
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

        return 
            $this->project
                ->fields()
                ->get()
                ->reduce(function ($carry, $field) {
                    if ($field->meta) {
                        return Str::replace(
                            "{field.meta.{$field->slug}}", 
                            $field->name, 
                            $carry
                        );
                    } else {
                        return Str::replace(
                            "{field.{$field->slug}}", 
                            $field->name, 
                            $carry
                        );
                    }
                }, $field->content);
    }
}