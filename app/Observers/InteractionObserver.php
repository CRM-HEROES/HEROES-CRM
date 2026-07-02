<?php

namespace App\Observers;

use App\Models\Interaction;
use App\Services\Ringover;
use App\Utils\ProjectSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InteractionObserver
{
    /**
     * Handle the Interaction "created" event.
     */
    public function created(Interaction $interaction): void
    {
        // $this->downloadAircallAudio($interaction);
        // $this->downloadRingoverAudio($interaction);
    }
    
    /**
     * Handle the Interaction "created" event.
     */
    public function updated(Interaction $interaction): void
    {
        // $this->downloadAircallAudio($interaction);
        // $this->downloadRingoverAudio($interaction);
    }
    
    /**
     * Download Aircall Audio
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function downloadAircallAudio(Interaction $interaction)
    {
        if ($interaction->source != 'aircall') {
            return;
        }
        
        if ($interaction->path) {
            return;
        }

        if (!is_array($interaction->data) || !isset($interaction->data['recording'])) {
            return;
        }

        $recording = $interaction->data['recording'];
        $name = Str::random(30) . '.' . pathinfo($recording)['extension'];
        $path = $interaction->prospect->project->slug . '/' . $name;

        // Download and Store content
        $size = $this->downloadAudio($recording, $path);

        // Update interaction
        $interaction->update([
            'path' => $path,
            'size' => $size
        ]);
    }
    
    /**
     * Download Aircall Audio
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function downloadRingoverAudio(Interaction $interaction)
    {
        if ($interaction->source != 'ringover') {
            return;
        }
        
        if ($interaction->path) {
            return;
        }

        if (!is_array($interaction->data)) {
            return;
        }

        if (!isset($interaction->data['id'])) {
            return;
        }

        if (!($settings = ProjectSetting::get($interaction->prospect->project, "ringover"))) {
            return;
        }

        Config::set([
            'ringover.api_token' => data_get($settings, 'api_token', null),
        ]);

        $ringover = app(Ringover::class);
        $call = $ringover->showCall($interaction->data['id']);

        if (!$call) {
            return;
        }

        if (!isset($call['recording'])) {
            return;
        }

        $recording = $call['recording'];
        $name = Str::random(30) . '.' . pathinfo($recording)['extension'];
        $path = $interaction->prospect->project->slug . '/' . $name;

        // Download and Store content
        $size = $this->downloadAudio($recording, $path);

        // Update interaction
        $interaction->update([
            'recording' => $recording,
            'path' => $path,
            'size' => $size
        ]);
    }
    
    /**
     * Download Aircall Audio
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function downloadAudio($source, $destination)
    {
        // Download and Store content
        $content = file_get_contents($source);
        $disk = Storage::disk('interactions');
        $disk->put($destination, $content);

        return $disk->size($destination);
    }
}
