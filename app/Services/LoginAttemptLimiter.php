<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class LoginAttemptLimiter
{
    const MAX_ATTEMPTS = 3;

    /**
     * Enregistre un échec de mot de passe. Retourne true si le compte vient d'être bloqué.
     * Un email inconnu est ignoré (rien à bloquer, et on ne révèle pas si le compte existe).
     */
    public static function recordFailure(?string $email): bool
    {
        if (!$email || !($user = User::where('email', $email)->first()) || $user->locked_at) {
            return false;
        }

        $attempts = $user->failed_login_attempts + 1;

        if ($attempts < self::MAX_ATTEMPTS) {
            $user->forceFill(['failed_login_attempts' => $attempts])->save();
            return false;
        }

        $user->forceFill(['failed_login_attempts' => $attempts, 'locked_at' => now()])->save();
        self::notify($user);

        return true;
    }

    public static function reset(User $user): void
    {
        if ($user->failed_login_attempts) {
            $user->forceFill(['failed_login_attempts' => 0])->save();
        }
    }

    /**
     * Débloque le compte puis envoie à l'utilisateur un email de réinitialisation de mot de passe.
     */
    public static function unlock(User $user): void
    {
        $user->forceFill(['failed_login_attempts' => 0, 'locked_at' => null])->save();

        try {
            $user->sendPasswordResetNotification(Password::createToken($user));
        } catch (\Throwable $e) {
            Log::error('Email de réinitialisation non envoyé à ' . $user->email . ' : ' . $e->getMessage());
        }
    }

    /**
     * Emails des administrateurs (créateurs) des projets de l'utilisateur ; à défaut, du créateur du compte.
     */
    private static function adminEmails(User $user): array
    {
        $ids = $user->projects()->pluck('projects.creator_id')
            ->push($user->creator_id)
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id == $user->id);

        return User::whereIn('id', $ids)->whereNotNull('email')->pluck('email')->unique()->values()->all();
    }

    public static function isLocked(?string $email): bool
    {
        return $email && User::where('email', $email)->whereNotNull('locked_at')->exists();
    }

    public static function lockedResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => 'account_locked',
            'message' => 'Votre compte est bloqué après ' . self::MAX_ATTEMPTS
                . ' tentatives de connexion échouées. Contactez l\'administrateur de votre projet pour le débloquer.',
        ], 403);
    }

    private static function notify(User $user): void
    {
        try {
            Mail::send('emails.account-locked', ['user' => $user, 'max' => self::MAX_ATTEMPTS, 'adminEmails' => self::adminEmails($user)], function ($mail) use ($user) {
                $mail->to($user->email)->subject('Votre compte a été bloqué');
            });
        } catch (\Throwable $e) {
            Log::error('Email de blocage non envoyé à ' . $user->email . ' : ' . $e->getMessage());
        }
    }
}
