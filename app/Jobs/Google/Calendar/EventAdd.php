<?php

namespace App\Jobs\Google\Calendar;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\GoogleAccount;
use App\Services\Google\Calendar as GoogleCalendar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventAdd implements ShouldQueue
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
        $calendar = $this->event->calendar;

        try {
            $googleCalendar->setAccessToken($this->googleAccount->token);
    
            if ($googleCalendar->isAccessTokenExpired()) {              
                $googleCalendar->fetchAccessTokenWithRefreshToken($googleCalendar->getRefreshToken());
                $token = $googleCalendar->getAccessToken();
                $googleCalendar->setAccessToken($token);    
                $this->googleAccount->update(['token' => $token]);
            }

			$service = new \Google_Service_Calendar($googleCalendar);

			// Create calendar if not exists

            $gCalendar = $this->googleAccount->calendars()->withPivot(['google_calendar_id', 'google_channel_id'])->find($calendar->id);
            
			if (!$gCalendar) {

                // create Google calendar
				$recurringCalendar = $this->createGoogleCalendarFrom($service, $calendar);
                $googleCalendarId = $recurringCalendar->getId();

                // try to watch google calendar events
                // from this created calendar
                try {
                    $watchChannel = $this->watchGoogleCalendarEvents($service, $googleCalendar, $recurringCalendar->getId());
                    $watchChannelId = $watchChannel->getId();
                } catch(\Exception $e) {   
                    $watchChannelId = null;
                }

                // register google calendar to GMI database
                $this->googleAccount->calendars()->syncWithoutDetaching([
                    $calendar->id => [
                        'google_calendar_id' => $googleCalendarId,
                        'google_channel_id' => $watchChannelId,
                    ]
                ]);

			} else {
                $googleCalendarId = $gCalendar->pivot->google_calendar_id;
            }

			// save event id into database

			$recurringEvent = $this->createGoogleEventFrom($service, $googleCalendarId, $this->event);

            $this->googleAccount->events()->syncWithoutDetaching([
                $this->event->id => [
                    'google_event_id' => $recurringEvent->getId(),
                    // hangout link
                    'hangout' => isset($recurringEvent->hangoutLink) && !empty($recurringEvent->hangoutLink) ? 
                        $recurringEvent->hangoutLink : null
                ]
            ]);
        } catch(\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * 
     * Create new calendar into Google Calendar
     * 
     * @param $service
     * 
     * @param $calendar
     *        BDD calendar from which we create the google calendar
     * 
     * @param google calendar ID
     * 
     */
    private function createGoogleCalendarFrom($service, Calendar $calendar) {

        $googleCalendar = new \Google_Service_Calendar_Calendar();

        // Summary
		$googleCalendar->setSummary($calendar->name);

        // Color
		// $googleCalendar->setColor($calendar->color);

		// Timezone
		$googleCalendar->setTimeZone($calendar->timezone);

		// add to Google Calendar
		$recurringCalendar = $service->calendars->insert($googleCalendar);

		return $recurringCalendar;
    }

    /**
     * Watch events update from Google Calendar
     *
     * @param $calendar
     *        calendar whose events are to be watched
     * 
     */
    private function watchGoogleCalendarEvents($service, GoogleCalendar $google, $googleCalendarId) {
        $channel =  new \Google_Service_Calendar_Channel($google);

        $uuid = $this->generate_uuid();
        $channel->setId($uuid);
        $channel->setType('web_hook');
        $channel->setAddress(route('google.event.webhook'));

        $watchChannel = $service->events->watch($googleCalendarId, $channel);

        return $watchChannel;
    }

    /**
     * 
     * Create new event into Google Calendar
     * 
     * @param $service
     * 
     * @param $event
     *        BDD event from which we create the google event
     * 
     * @param $googleCalendarId
     *        google calendar ID in which we create the event
     *        we must have a calendar per user per project
     * 
     */
    private function createGoogleEventFrom($service, $googleCalendarId, Event $event) {

        // send notification to attendees
        $extraParams = ['sendUpdates' => "all"];

    	// Create new Google Calendar Event
        $googleEvent = new \Google_Service_Calendar_Event();

        // Summary
		$googleEvent->setSummary($event->name);

        // Description
		$googleEvent->setDescription($event->description);

		// location
		if (!empty($event->location)) {
			$googleEvent->setLocation($event->location);
		}

		// start time
		$startDate = str_replace(' ', 'T', $event->started_at);
		$start = new \Google_Service_Calendar_EventDateTime();
		$start->setDateTime($startDate);
		$start->setTimeZone('Europe/Paris');
		$googleEvent->setStart($start);

		// end time
		$endDate = str_replace(' ', 'T', $event->ended_at);
		$end = new \Google_Service_Calendar_EventDateTime();
		$end->setDateTime($endDate);
		$end->setTimeZone('Europe/Paris');
		$googleEvent->setEnd($end);

		// Attendees

        $attendeeEmails = [];
        if ($event->email_to_prospect && $event->prospect && $event->prospect->email) {
            $attendeeEmails[] = $event->prospect->email;
        }

        foreach ($event->users as $user) {
            if ($user->email) {
                $attendeeEmails[] = $user->email;
            }
        }

        $attendees = array_map(function($email) {
            $attendee = new \Google_Service_Calendar_EventAttendee();
            $attendee->setEmail($email);

            return $attendee;
        }, $attendeeEmails);

        $googleEvent->attendees = $attendees;

        // Hangout

        $calendar = $event->calendar;
        if ($calendar) {
            if ($calendar->type == "hangout") {
                $extraParams['conferenceDataVersion'] = 1;
                $conferenceData = $this->createConferenceData();

                //set conference data in out event
                $googleEvent->setConferenceData($conferenceData);
            }
        }

		// add to Google Calendar
		$recurringEvent = $service->events->insert($googleCalendarId, $googleEvent, $extraParams);

		return $recurringEvent;
    }

    /**
     * 
     */
    private function createConferenceData()
    {

        //defining we have conference
        $conferenceData = new \Google_Service_Calendar_ConferenceData();

        //requesting google to create video meeting
        $req = new \Google_Service_Calendar_CreateConferenceRequest();
        // some random string/number changed every call. according to docs subsequent calls with the same request id are ignored
        $req->setRequestId('heroescrm_rq_' . time());
        //set request for videoconference in conference data
        $conferenceData->setCreateRequest($req);

        return $conferenceData;
    }

    /**
     * 
     */
    private function generate_uuid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}
