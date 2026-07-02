<?php

namespace App\Channels;

use App\Jobs\Google\Calendar\EventUpdate;
use App\Models\GoogleAccount;
use Illuminate\Notifications\Notification;

class GoogleCalendarUpdatedEventChannel
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
        EventUpdate::dispatchAfterResponse($googleAccount, $event)->onQueue('google_calendar');
    }
}
