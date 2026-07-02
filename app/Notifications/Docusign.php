<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class Docusign extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $document;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, Document $document)
    {
        $this->order = $order;
        $this->document = $document;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\DocusignChannel::class];
    }

    /**
     * 
     */
    public function toDocument($notifiable)
    {
        return $this->document;
    }
    
    /**
     * 
     */
    public function toOrder($notifiable)
    {
        return $this->order;
    }
    
}
