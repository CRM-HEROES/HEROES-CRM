<?php

namespace App\Channels;

use App\Jobs\SMS\SMSBOX;
use Illuminate\Notifications\Notification;

class SMSBOXChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sms = $notification->toSms($notifiable);
        SMSBOX::dispatch($sms)->onQueue('sms');
    }
}
