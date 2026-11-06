<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminAlert
{
    /**
     * Envoie un email à tous les super admins et garde une trace dans les logs.
     */
    public static function send(string $subject, string $message): void
    {
        Log::warning('[ALERTE SÉCURITÉ] ' . $subject . ' - ' . $message);

        $emails = User::where('role', 'super_admin')->pluck('email');

        foreach ($emails as $email) {
            try {
                Mail::raw($message, function ($mail) use ($email, $subject) {
                    $mail->to($email)->subject($subject);
                });
            } catch (\Throwable $e) {
                // Un email qui échoue ne doit jamais casser la connexion
                Log::error('Alerte non envoyée à ' . $email . ' : ' . $e->getMessage());
            }
        }
    }
}
