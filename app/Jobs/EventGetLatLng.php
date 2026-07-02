<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\Google\Map;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventGetLatLng implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Event $event;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(Map $map): void
    {
        $address = $this->event->location;
        if (!$address) {
            return;
        }

        $latLng = $this->getLatLng($map, $address);
        if (!$latLng) {
            return;
        }

        // Update event 
        // latitude and longitude
        $this->event->update($latLng);
    }
    
    /**
     * Get event latitude
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
            \ProjectLog::warning($this->event->calendar->project, $e->getMessage());
            return null;
        }

        if (count($search['candidates']) == 0) {
            \ProjectLog::warning($this->event->calendar->project, "Could not find address \"$address\" for the event ID: {$this->event->id}");
            return null;
        }

        $candidate = $search['candidates'][0];
        if (!isset($candidate['geometry']) || !$candidate['geometry']) {
            \ProjectLog::warning($this->event->calendar->project, "Could not find address \"$address\" for the event ID: {$this->event->id}");
            return null;
        }

        $geometry = $candidate['geometry'];
        if (!isset($geometry['location']) || !$geometry['location']) {
            \ProjectLog::warning($this->event->calendar->project, "Could not find address \"$address\" for the event ID: {$this->event->id}");
            return null;
        }

        $location = $geometry['location'];
        if (!isset($location['lat']) || !$location['lat'] || !isset($location['lng']) || !$location['lng']) {
            \ProjectLog::warning($this->event->calendar->project, "Could not find address \"$address\" for the event ID: {$this->event->id}");
            return null;
        }

        // latitude and longitude
        return [
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ];
    }

}
