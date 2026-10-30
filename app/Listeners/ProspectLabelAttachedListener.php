<?php

namespace App\Listeners;

use App\Events\ProspectLabelAttached;
use App\Notifications\PipedriveDealUpdatedProspectLabel;
use App\Services\ProspectLabelHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        $this->revision($event->prospect, $event->label, $event->oldValue, $event->createdAt);
        $this->checkUniqueCategory($event->prospect, $event->label);
        $this->pipedriveDealUpdated($event->prospect, $event->label);
    }

    /**
     * Insert revision row
     */
    protected function revision($prospect, $label, ?string $oldValue = null, ?string $createdAt = null)
    {
        $oldValue ??= $prospect
            ->labels()
            ->where('category_id', $label->category_id)
            ->where('labels.id', '!=', $label->id)
            ->get()
            ->pluck('name')
            ->implode(", ");

        $history = app(ProspectLabelHistory::class);
        $newValue = $label->category->unique ? $label->name : $history->value($prospect, $label);

        $history->record($prospect, $label, $oldValue, $newValue, $createdAt);
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
                    ->where('labels.id', '!=', $label->id)
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
