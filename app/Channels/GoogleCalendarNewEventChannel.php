<?php

namespace App\Channels;

use App\Jobs\Google\Calendar\EventAdd;
use App\Models\GoogleAccount;
use Illuminate\Notifications\Notification;

class GoogleCalendarNewEventChannel
{
    public function send($notifiable, Notification $notification)
    {
        $googleAccount = GoogleAccount::where([
            // User
            'user_id' => $notifiable->id,
            // For Google Calendar
            'for' => "calendar",
        ])
        ->first();

        if (!$googleAccount) {
            return;
        }
    
        $event = $notification->getEvent();
        EventAdd::dispatchAfterResponse($googleAccount, $event)->onQueue('google_calendar');
    }
}
