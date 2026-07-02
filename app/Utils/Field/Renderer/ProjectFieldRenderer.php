<?php

namespace App\Utils\Field\Renderer;

use App\Utils\Field\FieldRenderer;
use App\Models\Project;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Support\Str;

class ProjectFieldRenderer implements FieldRenderer
{
    protected Project $project;
    protected $defaultPattern = "/\\{project.(\\w+)\\}/";
    protected $metaPattern = "/\\{project.meta.(\\w+)\\}/";
    protected $defaultMatch = [];
    protected $metaMatch = [];
    
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Compute a content
     * 
     * @param  $content
     */
    public function render($content)
    {
        if (
            !preg_match_all($this->defaultPattern, $content, $this->defaultMatch) &&
            !preg_match_all($this->metaPattern, $content, $this->metaMatch)
        ) {
            return $content;
        }

        if (strpos($content, "{project.logo}") !== FALSE) {
            $thumbnail = (new LogoThumbnail($this->project, 200))->generate();
            
            if ($thumbnail) {
                $src = "data:image/jpeg;base64," . base64_encode($thumbnail);

                $content = Str::replace(
                    "{project.logo}", 
                    "<img src=\"{$src}\" style=\"width: 100%\" />", 
                    $content
                );
            }
        }

        $content = $this->project->fields->filter(function($field) {
            return $field->for == 'project' && (
                (!empty($this->defaultMatch) && !$field->meta && in_array($field->slug, $this->defaultMatch[1])) ||
                (!empty($this->metaMatch) && $field->meta && in_array($field->slug, $this->metaMatch[1]))
            );
        })
        ->reduce(function ($carry, $field) {
            return $field->meta ?
                Str::replace(
                    "{project.meta.{$field->slug}}", 
                    isset($this->project->meta[$field->slug]) ? $this->project->meta[$field->slug] : '', 
                    $carry
                )
                : Str::replace(
                    "{project.{$field->slug}}", 
                    $this->project->{$field->slug}, 
                    $carry
                );
        }, $content);

        return $content;
    }
}