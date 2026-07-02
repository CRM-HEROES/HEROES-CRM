<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Services\Google\Map;
use App\Utils\ProjectSetting;
use Illuminate\Support\Facades\Config;

class MapRenderer implements FieldRenderer
{
    protected Project $project;
    protected Prospect $prospect;
    protected ?User $user;

    public function __construct(Project $project, Prospect $prospect, ?User $user)
    {
        $this->project = $project;
        $this->prospect = $prospect;
        $this->user = $user;
    }

    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if ($field->type != "map") {
            return $field->content;
        }

        /*if (!$this->setSettings()) {
            return "";
        }*/

        $content = $field->content;
        if (!is_array($content)) {
            $content = json_decode($content);
        }

        $item = $this->getItem($content);

        if (
            !$item ||
            !$item->latitude ||
            !$item->longitude
        ) {
            return "";
        }

        $googleMap = app(Map::class);

        try {
            $image = $googleMap->staticMap(
                $item->latitude, 
                $item->longitude,
                intval(data_get($field->style, 'width', 400)),
                intval(data_get($field->style, 'height', 400)),
                data_get($content, 'zoom', 18),
                data_get($content, 'type', "satellite")
            );    
        } catch (\Exception $e) {
            \App\Utils\ProjectLog::error($this->project, $e->getMessage());
            return "";
        }

        $base64Image = base64_encode($image);

        return "<img style=\"width: 100%;\" src=\"data:image/png;base64, {$base64Image}\" />";
    }
    
    /**
     * Set map settings
     */
    protected function setSettings()
    {
        $settings = ProjectSetting::get($this->project, "google-map", null);

        if (!$settings) {
            \App\Utils\ProjectLog::error($this->project, "Attempting to retrieve a google map from PDF generation, no configuration for google map for the project is found.");
            return false;
        }

        Config::set([
            'google-map.api_key' => data_get($settings, 'api_key', ""),
        ]);

        return true;
    }

    /**
     * Get associated item
     */
    protected function getItem($content)
    {
        switch (data_get($content, 'for', "prospect")) {
            case "prospect":
                return $this->prospect;
            case "project":
                return $this->project;
            case "user":
                return $this->user;
            default:
                return null;
        }
    }

}