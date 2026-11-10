<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TemporaryPassword
{
    public static function issue(User $user): bool
    {
        $temporaryPassword = self::generate();
        $userName = trim(implode(' ', array_filter([$user->name, $user->last_name])));

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'password_temporary_expires_at' => now()->addHours(24),
        ]);

        $user->save();
        $user->tokens()->delete();

        try {
            Mail::raw(
                "Bonjour {$userName},\n\n" .
                "Votre accès à Heroes CRM a été créé.\n\n" .
                "Adresse de connexion : " . config('app.url') . "/login\n" .
                "Adresse email : {$user->email}\n" .
                "Mot de passe temporaire : {$temporaryPassword}\n" .
                "Ce mot de passe est valable pendant 24 heures.\n\n" .
                "Vous devrez le modifier à votre première connexion.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Votre accès à Heroes CRM');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Temporary password email could not be sent for user.', [
                'user_id' => $user->id,
            ]);

            return false;
        }

        return true;
    }

    protected static function generate(): string
    {
        $uppercase = str_split('ABCDEFGHJKLMNPRSTUVWXYZ');
        $lowercase = str_split('abcdefghijkmnpqrstuvwxyz');
        $digits = str_split('23456789');
        $symbols = str_split('!@#$%*?-_+=');

        $password = [
            $uppercase[random_int(0, count($uppercase) - 1)],
            $lowercase[random_int(0, count($lowercase) - 1)],
            $digits[random_int(0, count($digits) - 1)],
            $symbols[random_int(0, count($symbols) - 1)],
        ];

        $pool = array_merge($uppercase, $lowercase, $digits, $symbols);

        while (count($password) < 14) {
            $password[] = $pool[random_int(0, count($pool) - 1)];
        }

        for ($i = count($password) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$password[$i], $password[$j]] = [$password[$j], $password[$i]];
        }

        return implode('', $password);
    }
}
