<?php

namespace App\Channels;

use App\Jobs\OrderInvoiceSign;
use Illuminate\Notifications\Notification;

class DocusignChannel
{
    public function send($notifiable, Notification $notification)
    {
        $order = $notification->toOrder($notifiable);
        $document = $notification->toDocument($notifiable);
        OrderInvoiceSign::dispatch($order, $document)->onQueue('default');
    }
}
