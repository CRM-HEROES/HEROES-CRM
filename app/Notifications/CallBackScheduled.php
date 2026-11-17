<?php

namespace App\Notifications;

use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CallBackScheduled extends Notification
{
    use Queueable;

    public function __construct(
        protected Prospect $prospect,
        protected string $scheduledAt,
        protected string $reason,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rappel prospect à effectuer')
            ->greeting('Rappel prospect à effectuer')
            ->line(trim(implode(' ', array_filter([$this->prospect->first_name, $this->prospect->last_name]))) . ' (prospect #' . $this->prospect->id . ')')
            ->line($this->reason)
            ->line('Prochain créneau ouvré : ' . $this->scheduledAt);
    }
}
