<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\Google\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProjectGetLatLng implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Project $project;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(Map $map): void
    {
        $address = $this->project->address;
        if (!$address) {
            return;
        }

        $latLng = $this->getLatLng($map, $address);
        if (!$latLng) {
            return;
        }

        // Update project 
        // latitude and longitude
        $this->project->update($latLng);
    }
    
    /**
     * Get project latitude
     * and longitude
     * from street, postal code, city and country info
     *
     * @param  $address
     * @return void
     */
    protected function getLatLng(Map $map, $address)
    {
        try {
            $search = $map->find($address);
        } catch (\Exception $e) {
            \ProjectLog::warning($this->project, $e->getMessage());
            return null;
        }

        if (count($search['candidates']) == 0) {
            \ProjectLog::warning($this->project, "Could not find address \"$address\" for your project");
            return null;
        }

        $candidate = $search['candidates'][0];
        if (!isset($candidate['geometry']) || !$candidate['geometry']) {
            \ProjectLog::warning($this->project, "Could not find address \"$address\" for your project");
            return null;
        }

        $geometry = $candidate['geometry'];
        if (!isset($geometry['location']) || !$geometry['location']) {
            \ProjectLog::warning($this->project, "Could not find address \"$address\" for your project");
            return null;
        }

        $location = $geometry['location'];
        if (!isset($location['lat']) || !$location['lat'] || !isset($location['lng']) || !$location['lng']) {
            \ProjectLog::warning($this->project, "Could not find address \"$address\" for your project");
            return null;
        }

        // latitude and longitude
        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ];
    }

}
