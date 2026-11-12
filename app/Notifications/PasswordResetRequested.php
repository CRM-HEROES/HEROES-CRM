<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email de confirmation d'un changement de mot de passe : le lien mène à la page
 * "choisir un nouveau mot de passe" du CRM. Le mot de passe actuel reste valable tant que le lien n'est pas utilisé.
 */
class PasswordResetRequested extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        $url = rtrim(config('app.url'), '/') . '/password/reset/' . $this->token
            . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Changement de votre mot de passe Heroes CRM')
            ->greeting('Bonjour,')
            ->line('Une demande de changement de mot de passe a été faite pour votre compte Heroes CRM.')
            ->action('Choisir un nouveau mot de passe', $url)
            ->line("Ce lien est personnel, utilisable une seule fois et valable {$minutes} minutes.")
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez cet email : votre mot de passe actuel reste inchangé.");
    }
}
