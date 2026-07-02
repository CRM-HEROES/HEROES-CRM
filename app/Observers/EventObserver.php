<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\User;
use App\Jobs\EventGetLatLng;
use App\Jobs\UserGetEventsDailyDirection;
use App\Notifications\GoogleCalendarDeletedEvent;
use App\Notifications\GoogleCalendarNewEvent;
use App\Notifications\GoogleCalendarUpdatedEvent;

class EventObserver
{
    /**
     * Handle the Event "creating" event.
     */
    public function creating(Event $event): void
    {
        $this->setCreatingConfirmedBy($event);
        $this->setCreatingDoneBy($event);
    }
    
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        // Creator
        $this->attachProspectUser($event);
        $this->updateProspectAddress($event);
        $this->getLatLng($event);
        $this->getUserEventsDailyDirection($event);
        $this->storeGoogleCalendar($event);
    }
    
    /**
     * Handle the Event "updating" event.
     */
    public function updating(Event $event): void
    {
        $this->setUpdatingConfirmedBy($event);
        $this->setUpdatingDoneBy($event);
    }
    
    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        $this->getLatLng($event);
        $this->getUpdateUserEventsDailyDirection($event);
        $this->updateGoogleCalendar($event);
    }
    
    /**
     * Handle the Event "deleting" event.
     */
    public function deleting(Event $event): void
    {
        $this->removeGoogleCalendar($event);
    }
    
    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        $this->getUserEventsDailyDirection($event);
    }
    
    /**
     * Attach event prospect
     * to the event affected user
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function attachProspectUser(Event $event)
    {
        $event->calendar->users()->syncWithoutDetaching($event->user_id);
        
        if (!$event->prospect_id) {
            return;
        }

        if (!$event->user_id) {
            return;
        }

        $event->prospect->users()->syncWithoutDetaching($event->user_id);
    }
    
    /**
     * Attach event prospect
     * to the event affected user
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function updateProspectAddress(Event $event)
    {
        if (
            !$event->location ||
            !$event->prospect ||
            $event->prospect->street
        ) {
            return;
        }

        $event->prospect->update([
            'street' => $event->location
        ]);
    }
    
    /**
     * Get project latitude
     * and longitude
     * from street, postal code, city and country info
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function getLatLng(Event $event)
    {
        if (!$event->isDirty('location')) {
            return;
        }

        // $event->syncOriginal(['location']);
        EventGetLatLng::dispatchAfterResponse($event);
    }
    
    /**
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function setUpdatingConfirmedBy(Event $event)
    {
        if (!$event->isDirty('confirmed')) {
            return;
        }

        if (!$event->confirmed) {
            return;
        }

        $event->confirmed_by = auth()->id();
    }
    
    
    /**
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function setUpdatingDoneBy(Event $event)
    {
        if (!$event->isDirty('done')) {
            return;
        }

        if (!$event->done) {
            return;
        }

        $event->done_by = auth()->id();
    }
    
    /**
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function setCreatingConfirmedBy(Event $event)
    {
        if (!$event->confirmed) {
            return;
        }

        $event->confirmed_by = auth()->id();
    }
    
    
    /**
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function setCreatingDoneBy(Event $event)
    {
        if (!$event->done) {
            return;
        }

        $event->done_by = auth()->id();
    }
    
    /**
     * Get project latitude
     * and longitude
     * from street, postal code, city and country info
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function getUpdateUserEventsDailyDirection(Event $event)
    {
        if ($event->calendar->type != 'physical') {
            return;
        }

        if (
            $event->isDirty('user_id') || 
            $event->isDirty('started_at') || 
            $event->isDirty('location')
        ) {
            $oldUser = User::find($event->getOriginal('user_id'));
            $oldDate = substr($event->getOriginal('started_at'), 0, 10);
    
            $event->syncOriginal(['user_id', 'started_at', 'location']);
            UserGetEventsDailyDirection::dispatchAfterResponse(
                // $event->calendar->project, 
                $oldUser,
                $oldDate
            );

            ///////////////////////////////////////
            
            $newUser = $event->user;
            $newDate = substr($event->started_at, 0, 10);
            if (
                $oldDate == $newDate && 
                $oldUser->id == $newUser->id
            ) {
                return;
            }
        
            UserGetEventsDailyDirection::dispatchAfterResponse(
                // $event->calendar->project, 
                $newUser, 
                $newDate
            );
        }
    }
    
    /**
     * Get project latitude
     * and longitude
     * from street, postal code, city and country info
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function getUserEventsDailyDirection(Event $event)
    {
        if ($event->calendar->type != 'physical') {
            return;
        }

        UserGetEventsDailyDirection::dispatchAfterResponse(
            // $event->calendar->project, 
            $event->user, 
            substr($event->started_at, 0, 10)
        );
    }
    
    /**
     * Add event to
     * the event user google calendar
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function storeGoogleCalendar(Event $event)
    {
        // To do
        $event->user->notify(new GoogleCalendarNewEvent($event));
    }
    
    /**
     * Update event user google calendar
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function updateGoogleCalendar(Event $event)
    {
        // To do
        $event->user->notify(new GoogleCalendarUpdatedEvent($event));
    }
    
    /**
     * Update event user google calendar
     *
     * @param  \App\Models\Event  $event
     * @return void
     */
    protected function removeGoogleCalendar(Event $event)
    {
        // To do
        $event->user->notify(new GoogleCalendarDeletedEvent($event));
    }
}
