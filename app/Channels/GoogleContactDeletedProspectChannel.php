<?php

namespace App\Channels;

use App\Jobs\Google\Contact\ContactDelete;
use App\Models\GoogleAccount;
use Illuminate\Notifications\Notification;

class GoogleContactDeletedProspectChannel
{
    public function send($notifiable, Notification $notification)
    {
        $googleAccount = GoogleAccount::where([
            // User
            'user_id' => $notifiable->id,
            // For Google Calendar
            'for' => "contact",
        ])
        ->first();

        if (!$googleAccount) {
            return;
        }
    
        $prospect = $notification->getProspect();
        ContactDelete::dispatchAfterResponse($googleAccount, $prospect)->onQueue('google_contact');
    }
}
