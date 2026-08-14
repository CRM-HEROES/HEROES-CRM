<?php

namespace App\Utils\Field\Renderer;

use App\Utils\Field\FieldRenderer;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Support\Str;

class ProspectFieldRenderer implements FieldRenderer
{
    protected Project $project;
    protected Prospect $prospect;
    protected $defaultPattern = "/\\{prospect.(\\w+)\\}/";
    protected $metaPattern = "/\\{prospect.meta.(\\w+)\\}/";
    protected $defaultMatch = [];
    protected $metaMatch = [];
    
    public function __construct(Project $project, Prospect $prospect)
    {
        $this->project = $project;
        $this->prospect = $prospect;
    }

    /**
     * Compute a content
     * 
     * @param  $content
     */
    public function render($content)
    {
        // Both patterns must always be evaluated: with && short-circuiting,
        // a content string containing both a {prospect.x} and a
        // {prospect.meta.x} token only ever had defaultMatch populated,
        // silently skipping every meta token replacement below.
        $hasDefaultMatch = preg_match_all($this->defaultPattern, $content, $this->defaultMatch);
        $hasMetaMatch = preg_match_all($this->metaPattern, $content, $this->metaMatch);

        if (!$hasDefaultMatch && !$hasMetaMatch) {
            return $content;
        }

        // Prospect full name
        $content = Str::replace(
            "{prospect.full_name}", 
            $this->prospect->full_name, 
            $content
        );

        // Prospect full address
        $content = Str::replace(
            "{prospect.address}", 
            $this->prospect->address, 
            $content
        );

        return $this->project->fields->filter(function($field) {
            return $field->for == 'prospect' && (
                (!empty($this->defaultMatch) && !$field->meta && in_array($field->slug, $this->defaultMatch[1])) ||
                (!empty($this->metaMatch) && $field->meta && in_array($field->slug, $this->metaMatch[1]))
            );
        })
        ->reduce(function ($carry, $field) {
            return $field->meta ?
                Str::replace(
                    "{prospect.meta.{$field->slug}}", 
                    isset($this->prospect->meta[$field->slug]) ? $this->prospect->meta[$field->slug] : '', 
                    $carry
                )
                : Str::replace(
                    "{prospect.{$field->slug}}", 
                    $this->prospect->{$field->slug}, 
                    $carry
                );
        }, $content);
    }
}