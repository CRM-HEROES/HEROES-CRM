<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

class UserRegistration extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {}

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
        $url = MagicLink::create(
            new LoginAction($notifiable, redirect()->route('user.registration.info'))
        )->url;

        return (new MailMessage)
                    ->subject("Inscription sur HeroesCRM.")
                    ->view('emails.user-registration', [
                        'user' => $notifiable,
                        'url' => $url,
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
