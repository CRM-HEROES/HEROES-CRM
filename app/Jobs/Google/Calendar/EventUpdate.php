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

class EventUpdate implements ShouldQueue
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
        $gEvent = $this->googleAccount->events()->withPivot('google_event_id')->find($this->event->id);
    	if (!$gEvent) {
        	return;
        }

        $gCalendar = $this->googleAccount->calendars()->withPivot('google_calendar_id')->find($this->event->calendar->id);
        if (!$gCalendar) {
            return;
        }

		try {
            $googleCalendar->setAccessToken($this->googleAccount->token);
    
            if ($googleCalendar->isAccessTokenExpired()) {              
                $googleCalendar->fetchAccessTokenWithRefreshToken($googleCalendar->getRefreshToken());
                $token = $googleCalendar->getAccessToken();
                $googleCalendar->setAccessToken($token);    
                $this->googleAccount->update(['token' => $token]);
            }

			$service = new \Google_Service_Calendar($googleCalendar);

			// Retrieve Google event from Google Calendar
            $googleCalendarId = $gCalendar->pivot->google_calendar_id;
	        $googleEvent = $service->events->get($googleCalendarId, $gEvent->pivot->google_event_id);

	        // Summary
			$googleEvent->setSummary($this->event->name);

	        // Description
			$googleEvent->setDescription($this->event->description);

			// location
            $googleEvent->setLocation($this->event->location);

			// start time
			$startDate = str_replace(' ', 'T', $this->event->started_at);
			$start = new \Google_Service_Calendar_EventDateTime();
			$start->setDateTime($startDate);
			$start->setTimeZone('Europe/Paris');
			$googleEvent->setStart($start);

			// end time
			$endDate = str_replace(' ', 'T', $this->event->ended_at);
			$end = new \Google_Service_Calendar_EventDateTime();
			$end->setDateTime($endDate);
			$end->setTimeZone('Europe/Paris');
			$googleEvent->setEnd($end);

			// update in Google Calendar
            $service->events->update($googleCalendarId, $googleEvent->getId(), $googleEvent, ['sendNotifications' => true]);

            $this->googleAccount->events()->updateExistingPivot($this->event->id, ['updated_at' => \Carbon\Carbon::now()]);
		} catch(\Exception $e) {
            throw $e;
        }
    }
}
