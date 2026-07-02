<?php

namespace App\Listeners;

use App\Events\ProspectLabelAttached;
use App\Notifications\PipedriveDealUpdatedProspectLabel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Venturecraft\Revisionable\Revision;

class ProspectLabelAttachedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProspectLabelAttached $event): void
    {
        $this->revision($event->prospect, $event->label);
        $this->checkUniqueCategory($event->prospect, $event->label);
        $this->pipedriveDealUpdated($event->prospect, $event->label);
    }

    /**
     * Insert revision row
     */
    protected function revision($prospect, $label)
    {
        $oldValue = $prospect
            ->labels()
            ->where('category_id', $label->category_id)
            ->where('id', '!=', $label->id)
            ->get()
            ->pluck('name')
            ->implode(", ");

        $newValue = $prospect
            ->labels()
            ->where('category_id', $label->category_id)
            ->get()
            ->pluck('name')
            ->implode(", ");

        Revision::insert([
            'revisionable_type' => get_class($prospect),
            'revisionable_id' => $prospect->getKey(),
            'key' => "category->" . $label->category_id,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => auth()->id(),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    /**
     * 
     */
    protected function checkUniqueCategory($prospect, $label) {
        // Category unique label choice
        // Remove previous selected label
        if (!$label->category->unique) {
            return;
        }

        $prospect
            ->labels()
            ->detach(
                $prospect
                    ->labels()
                    ->where('category_id', $label->category_id)
                    ->where('id', '!=', $label->id)
                    ->get()
                    ->pluck('id')
                    ->toArray()
                );
    }

    /**
     * 
     */
    protected function pipedriveDealUpdated($prospect, $label) {
        $prospect->project->notify(new PipedriveDealUpdatedProspectLabel($prospect, $label));
    }
}
