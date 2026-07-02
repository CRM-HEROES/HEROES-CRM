<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\Category;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Support\Str;

class ProspectCategoryRenderer implements FieldRenderer
{
    protected $types = ['text', 'html', 'qrcode', 'order-table'];
    protected Project $project;
    protected Prospect $prospect;
    
    public function __construct(Project $project, Prospect $prospect)
    {
        $this->project = $project;
        $this->prospect = $prospect;
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

        $out = [];
        preg_match_all("/\\{prospect.category.(\\d+)\\}/", $field->content, $out, PREG_PATTERN_ORDER);

        if (empty($out)) {
            return $field->content;
        }

        return $this->project
            ->categories
            ->whereIn('id', $out[1])
            ->reduce(function ($carry, $category) {
                $labels = $this->prospect
                    ->labels()
                    ->where('category_id', $category->id)
                    ->get()
                    ->map(function($label) {
                        return $label->name;
                    })
                    ->join(', ');

                return Str::replace(
                    "{prospect.category.{$category->id}}", 
                    $labels, 
                    $carry
                );
            }, $field->content);
    }
}