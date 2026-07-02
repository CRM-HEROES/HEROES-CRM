<?php

namespace App\Utils\Field\Renderer;

use App\Utils\Field\FieldRenderer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class UserFieldRenderer implements FieldRenderer
{
    protected Project $project;
    protected ?User $user;
    protected $defaultPattern = "/\\{user.(\\w+)\\}/";
    protected $metaPattern = "/\\{user.meta.(\\w+)\\}/";
    protected $defaultMatch = [];
    protected $metaMatch = [];
    
    public function __construct(Project $project, ?User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     * Compute a content
     * 
     * @param  mixed  $content
     * @return mixed
     */
    public function render($content)
    {
        if (!$this->user) {
            return $content;
        }

        if (
            !preg_match_all($this->defaultPattern, $content, $this->defaultMatch) &&
            !preg_match_all($this->metaPattern, $content, $this->metaMatch)
        ) {
            return $content;
        }

        return $this->project->fields->filter(function($field) {
            return $field->for == 'user' && (
                (!empty($this->defaultMatch) && !$field->meta && in_array($field->slug, $this->defaultMatch[1])) ||
                (!empty($this->metaMatch) && $field->meta && in_array($field->slug, $this->metaMatch[1]))
            );
        })
        ->reduce(function ($carry, $field) {
            return $field->meta
                ? Str::replace(
                    "{user.meta.{$field->slug}}", 
                    isset($this->user->meta[$field->slug]) ? $this->user->meta[$field->slug] : '', 
                    $carry
                )
                : Str::replace(
                    "{user.{$field->slug}}", 
                    $this->user->{$field->slug}, 
                    $carry
                );
        }, $content);
    }
}
