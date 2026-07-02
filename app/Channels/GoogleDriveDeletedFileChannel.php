<?php

namespace App\Channels;

use App\Jobs\Google\Drive\FileDelete;
use App\Models\GoogleAccount;
use Illuminate\Notifications\Notification;

class GoogleDriveDeletedFileChannel
{
    public function send($notifiable, Notification $notification)
    {
        $googleAccount = GoogleAccount::where([
            // User
            'project_id' => $notifiable->id,
            // For Google Calendar
            'for' => "drive",
        ])
        ->first();

        if (!$googleAccount) {
            return;
        }
    
        $file = $notification->getFile();
        FileDelete::dispatch($googleAccount, $file)->onQueue('google_drive');
    }
}
