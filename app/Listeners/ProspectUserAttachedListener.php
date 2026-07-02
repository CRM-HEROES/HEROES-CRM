<?php

namespace App\Listeners;

use App\Events\ProspectUserAttached;
use App\Notifications\GoogleContactNewProspect;
use App\Notifications\PipedriveDealUpdatedProspectLabel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Venturecraft\Revisionable\Revision;

class ProspectUserAttachedListener
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
    public function handle(ProspectUserAttached $event): void
    {
        $this->googleContact($event->prospect, $event->user);
    }

    /**
     * Insert revision row
     */
    protected function googleContact($prospect, $user)
    {
        $user->notify(new GoogleContactNewProspect($prospect));
    }
}
