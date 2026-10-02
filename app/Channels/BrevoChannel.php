<?php

namespace App\Channels;

use App\Jobs\SMS\Brevo;
use Illuminate\Notifications\Notification;

class BrevoChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sms = $notification->toSms($notifiable);
        Brevo::dispatch($sms)->onQueue('sms');
    }
}