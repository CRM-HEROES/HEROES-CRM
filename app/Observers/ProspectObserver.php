<?php

namespace App\Observers;

use App\Models\Prospect;
use App\Jobs\ProspectGetLatLng;
use App\Notifications\GoogleContactDeletedProspect;
use App\Notifications\GoogleContactNewProspect;
use App\Notifications\PipedrivePersonNewProspect;
use App\Notifications\PipedrivePersonUpdatedProspect;
use Illuminate\Support\Facades\DB;
use Venturecraft\Revisionable\Revision;

class ProspectObserver
{
    /**
     * Handle the Prospect "creating" event.
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    public function creating(Prospect $prospect)
    {
        $this->checkDefaultValue($prospect);
    }

    /**
     * Handle the Prospect "created" event.
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    public function created(Prospect $prospect)
    {
        $this->attachToCurrentUser($prospect);
        $this->getLatLng($prospect);
        $this->storeGoogleContact($prospect);
        $this->storePipedrivePerson($prospect);
    }

    /**
     * Handle the Prospect "updating" event.
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    public function updating(Prospect $prospect)
    {
        $this->checkUniqueValue($prospect);
    }

    /**
     * Handle the Prospect "updated" event.
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    public function updated(Prospect $prospect)
    {
        $this->getLatLng($prospect);
        $this->processed($prospect);
        $this->checkMetaFieldUpdated($prospect);
        $this->updatePipedrivePerson($prospect);
    }

    /**
     * Check default value
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function checkDefaultValue(Prospect $prospect)
    {
        $meta = $prospect->meta;
        foreach (
            $prospect->project->fields()->where('for', 'prospect')->whereNotNull('default_value')->get(['id', 'slug', 'meta', 'default_value']) as $field
        ) {
            if ($field->meta && $meta && !$meta[$field->slug]) {
                $meta[$field->slug] = $field->default_value;
            } else if (!$field->meta && !$prospect[$field->slug]) {
                $prospect[$field->slug] = $field->default_value;
            }
        }
        $prospect->meta = $meta;
    }

    /**
     * Check unique value
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function checkUniqueValue(Prospect $prospect)
    {
        foreach (
            $prospect->project->fields()
                ->where('for', 'prospect')
                ->where('meta', 0)
                ->where('unique', 1)
                ->get(['id', 'slug', 'name']) as $field
        ) {
            if (!$prospect->isDirty($field->slug) || empty($prospect->{$field->slug})) {
                continue;
            }
        
            $d = Prospect::
                where('id', '!=', $prospect->id)
                ->where($field->slug, $prospect->{$field->slug})
                ->first(['id', 'full_name']);

            if ($d) {
                abort(400, 'Valeur dupliquée pour la colonne: ' . $field->name . ', Prospect: ' . $d->full_name);
            }
        }
    }

    /**
     * Get prospect latitude
     * and longitude
     * from street, postal code, city and country info
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function getLatLng(Prospect $prospect)
    {
        if (
            $prospect->isDirty('latitude') &&
            $prospect->isDirty('longitude')
        ) {
            $prospect->syncOriginal(['latitude', 'longitude']);
            $prospect->update(['valid_address' => 1]);
            return;
        }

        if (
            !$prospect->isDirty('street') &&
            !$prospect->isDirty('street_bis') &&
            !$prospect->isDirty('postal_code') &&
            !$prospect->isDirty('city') &&
            !$prospect->isDirty('country')
        ) {
            return;
        }

        $prospect->syncOriginal(['street', 'street_bis', 'postal_code', 'city', 'country']);
        ProspectGetLatLng::dispatch($prospect)->onQueue("google_map");
    }

    /**
     * When processed by is updated
     * Set processed at
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function processed(Prospect $prospect)
    {
        if (
            !$prospect->isDirty('processed_by')
        ) {
            return;
        }

        $prospect->syncOriginal(['processed_by']);
        $prospect->update([
            'processed_at' => \Carbon\Carbon::now()
        ]);
    }

    /**
     * Attach prospect to current user
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function attachToCurrentUser(Prospect $prospect)
    {
        if (!auth()->user()) {
            return;
        }

        $prospect->users()->syncWithoutDetaching(auth()->id());
    }

    /**
     * Store to user google contact
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function storeGoogleContact(Prospect $prospect)
    {
        if (!$prospect->creator) {
            return;
        }

        // To do
        // $prospect->creator->notify(new GoogleContactNewProspect($prospect));
    }

    /**
     * Delete from user google contact
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function deleteGoogleContact(Prospect $prospect)
    {
        if (!$prospect->creator) {
            return;
        }

        // To do
        // $prospect->creator->notify(new GoogleContactDeletedProspect($prospect));
    }
    
    /**
     * Store to pipedrive
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function storePipedrivePerson(Prospect $prospect)
    {
        $prospect->project->notify(new PipedrivePersonNewProspect($prospect));
    }

    /**
     * Update pipedrive
     *
     * @param  \App\Models\Prospect  $prospect
     * @return void
     */
    protected function updatePipedrivePerson(Prospect $prospect)
    {
        $prospect->project->notify(new PipedrivePersonUpdatedProspect($prospect));
    }

    /**
     * Prospect Revision
     */
    private function checkMetaFieldUpdated($prospect)
    {
        if (!$prospect->isDirty('meta')) {
        	return;
        }

        // add revision

        $oldMeta = $prospect->getOriginal('meta');

        $revisions = [];
        foreach ($prospect->meta as $field => $newValue) {
            $oldValue = isset($oldMeta[$field]) ? $oldMeta[$field] : "";

            if ($newValue != $oldValue) {
                $revisions[] = [
                    'revisionable_type' => get_class($prospect),
                    'revisionable_id' => $prospect->getKey(),
                    'key' => "meta->{$field}",
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'user_id' => auth()->id(),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
            }
        }

        if (count($revisions)) {
            Revision::insert($revisions);
        }
    }

}
