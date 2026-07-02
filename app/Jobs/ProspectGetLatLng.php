<?php

namespace App\Jobs;

use App\Models\Prospect;
use App\Services\Google\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProspectGetLatLng implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Prospect $prospect;

    /**
     * Create a new job instance.
     */
    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * Execute the job.
     */
    public function handle(Map $map): void
    {
        $address = $this->prospect->address;
        if (!$address) {
            return;
        }

        $latLng = $this->getLatLng($map, $address);
        if (!$latLng) {
            $this->prospect->update(['valid_address' => false]);
            return;
        }

        // Update prospect 
        // latitude and longitude
        $this->prospect->update(array_merge(
            $latLng, 
            ['valid_address' => true]
        ));
    }
    
    /**
     * Get prospect latitude
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
            \ProjectLog::error($this->prospect->project, $e->getMessage());
            return null;
        }

        if (count($search['candidates']) == 0) {
            \ProjectLog::warning($this->prospect->project, "Could not find address \"$address\" for the prospect ID: {$this->prospect->id}");
            return null;
        }

        $candidate = $search['candidates'][0];
        if (!isset($candidate['geometry']) || !$candidate['geometry']) {
            \ProjectLog::warning($this->prospect->project, "Could not find address \"$address\" for the prospect ID: {$this->prospect->id}");
            return null;
        }

        $geometry = $candidate['geometry'];
        if (!isset($geometry['location']) || !$geometry['location']) {
            \ProjectLog::warning($this->prospect->project, "Could not find address \"$address\" for the prospect ID: {$this->prospect->id}");
            return null;
        }

        $location = $geometry['location'];
        if (!isset($location['lat']) || !$location['lat'] || !isset($location['lng']) || !$location['lng']) {
            \ProjectLog::warning($this->prospect->project, "Could not find address \"$address\" for the prospect ID: {$this->prospect->id}");
            return null;
        }

        // latitude and longitude
        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ];
    }

}
