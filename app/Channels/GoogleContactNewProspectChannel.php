<?php

namespace App\Channels;

use App\Jobs\Google\Contact\ContactAdd;
use App\Models\GoogleAccount;
use Illuminate\Notifications\Notification;

class GoogleContactNewProspectChannel
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
        ContactAdd::dispatchAfterResponse($googleAccount, $prospect)->onQueue('google_contact');
    }
}
