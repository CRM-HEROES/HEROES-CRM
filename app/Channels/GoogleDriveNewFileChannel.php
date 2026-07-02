<?php

namespace App\Channels;

use App\Jobs\Google\Drive\FileAdd;
use App\Models\GoogleAccount;
use Illuminate\Notifications\Notification;

class GoogleDriveNewFileChannel
{
    public function send($notifiable, Notification $notification)
    {
        $googleAccount = GoogleAccount::where([
            // Project
            'project_id' => $notifiable->id,
            // For Google Calendar
            'for' => "drive",
        ])
        ->first();

        if (!$googleAccount) {
            return;
        }
    
        $file = $notification->getFile();
        FileAdd::dispatchAfterResponse($googleAccount, $file)->onQueue('google_drive');
    }
}
