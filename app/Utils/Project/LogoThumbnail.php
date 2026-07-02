<?php

namespace App\Utils\Project;

use App\Models\Project;
use App\Utils\File\ImageThumbnail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoThumbnail
{
    protected Project $project;
    protected $size;
    
    public function __construct(Project $project, $size = 150)
    {
        $this->project = $project;
        $this->size = $size;
    }

    /**
     * Render a project thumbnail
     * 
     * @param  content
     */
    public function generate()
    {
        if (!$this->project->logo) {
            return null;
        }

        $disk = Storage::disk('logos');
        $pathinfo = pathinfo($this->project->logo);

        switch (Str::lower($pathinfo['extension'])) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
                return LogoThumbnail::image($disk, $this->project, $this->size);
            default:
                return null;
        }
    }

    static public function image($disk, $project, $maxSize)
    {
        $pathinfo = pathinfo($project->logo);
        $thumbnail = $pathinfo['dirname'] . "/" . $pathinfo['filename'] . "_{$maxSize}.thumb.jpg";
        
        if ($disk->exists($thumbnail)) {
            return $disk->get($thumbnail);
        }

        try {
            $imagick = ImageThumbnail::generate($disk->path($project->logo), $maxSize);
            $imagick->writeImage($disk->path($thumbnail));

            return $disk->get($thumbnail);
        } catch (\Exception $e) {
            return null;
        }
    }
}