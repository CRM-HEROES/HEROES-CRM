<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/**
 * Invitation d'un nouvel utilisateur : il reçoit un LIEN (jamais un mot de passe) et choisit
 * lui-même son mot de passe. Le lien est signé, expire, et ne marche qu'une fois.
 *
 * Le compte reste "en attente" (must_change_password = true) tant que le mot de passe n'est pas choisi.
 */
class UserInvitation
{
    public const VALID_HOURS = 48;

    /**
     * (Ré)envoie une invitation. Tout lien précédent devient invalide, car le mot de passe
     * (inconnu de tous) est remplacé et le lien dépend de lui.
     *
     * @return bool false si l'email n'a pas pu partir
     */
    public static function send(User $user): bool
    {
        $expiresAt = now()->addHours(self::VALID_HOURS);

        $user->forceFill([
            'password' => Hash::make(bin2hex(random_bytes(32))),
            'must_change_password' => true,
            'password_temporary_expires_at' => $expiresAt,
        ])->save();

        $user->tokens()->delete();

        $link = self::link($user, $expiresAt);
        $userName = trim(implode(' ', array_filter([$user->name, $user->last_name])));

        try {
            Mail::raw(
                "Bonjour {$userName},\n\n"
                . "Un compte Heroes CRM a été créé pour vous.\n\n"
                . "Pour l'activer et choisir votre mot de passe, cliquez sur ce lien :\n{$link}\n\n"
                . 'Ce lien est personnel, utilisable une seule fois et valable ' . self::VALID_HOURS . " heures.\n"
                . "Votre identifiant de connexion est votre adresse email : {$user->email}\n\n"
                . "Si vous n'attendiez pas ce message, ignorez-le : aucun accès n'est ouvert tant que le lien n'est pas utilisé.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Activez votre accès à Heroes CRM');
                }
            );
        } catch (\Throwable $e) {
            Log::error("Invitation non envoyée à l'utilisateur {$user->id} : " . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Empreinte du mot de passe actuel : dès qu'il change (activation ou nouvelle invitation),
     * les anciens liens ne correspondent plus.
     */
    public static function version(User $user): string
    {
        return substr(hash_hmac('sha256', (string) $user->password, (string) config('app.key')), 0, 20);
    }

    /**
     * Lien de la page "Choisir mon mot de passe" du CRM. Sa chaîne de paramètres est la signature
     * de la route API d'activation : la page la renvoie telle quelle.
     */
    private static function link(User $user, $expiresAt): string
    {
        $prefix = Route::has('api.invitation.accept') ? 'api.' : '';

        $signed = URL::temporarySignedRoute($prefix . 'invitation.accept', $expiresAt, [
            'user' => $user->id,
            'v' => self::version($user),
        ]);

        return rtrim(config('app.url'), '/') . '/invitation/' . $user->id . '?' . parse_url($signed, PHP_URL_QUERY);
    }
}
