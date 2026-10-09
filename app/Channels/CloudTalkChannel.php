<?php

namespace App\Channels;

use App\Jobs\SMS\CloudTalk;
use Illuminate\Notifications\Notification;

class CloudTalkChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sms = $notification->toSms($notifiable);
        CloudTalk::dispatch($sms)->onQueue('sms');
    }
}
