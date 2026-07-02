<?php

namespace App\Jobs\Google\Calendar;

use App\Models\Event;
use App\Models\GoogleAccount;
use App\Services\Google\Calendar as GoogleCalendar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected GoogleAccount $googleAccount;
    protected Event $event;

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleAccount $googleAccount, Event $event)
    {
        $this->googleAccount = $googleAccount;
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendar $googleCalendar): void
    {
        // check if event has a google event id
        $gEvent = $this->googleAccount->events()->withTrashed()->withPivot('google_event_id')->find($this->event->id);
    	if (!$gEvent) {
            return;
        }
        $this->googleAccount->events()->detach($this->event->id);

        // check if calendar has a google calendar id
        $gCalendar = $this->googleAccount->calendars()->withPivot('google_calendar_id')->find($this->event->calendar_id);
        if (!$gCalendar) {
            return;
        }

        $googleCalendar->setAccessToken($this->googleAccount->token);
    
        if ($googleCalendar->isAccessTokenExpired()) {              
            $googleCalendar->fetchAccessTokenWithRefreshToken($googleCalendar->getRefreshToken());
            $token = $googleCalendar->getAccessToken();
            $googleCalendar->setAccessToken($token);    
            $this->googleAccount->update(['token' => $token]);
        }

        $service = new \Google_Service_Calendar($googleCalendar);


        // Remove event from Google Calendar

        $service->events->delete(
            $gCalendar->pivot->google_calendar_id, 
            $gEvent->pivot->google_event_id, 
            ['sendNotifications' => true]
        );

        $this->googleAccount->events()->detach([$this->event->id]);
    }
}
