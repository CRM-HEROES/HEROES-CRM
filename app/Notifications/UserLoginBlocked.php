<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserLoginBlocked extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Compte HeroesCRM bloqué')
            ->greeting('Bonjour,')
            ->line('Votre compte a été bloqué après trois échecs consécutifs de connexion.')
            ->line('Veuillez contacter votre administrateur pour le débloquer.');
    }
}
