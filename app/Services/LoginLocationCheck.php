<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Stevebauman\Location\Facades\Location;

class LoginLocationCheck
{
    /**
     * À appeler après une connexion réussie.
     * Ne doit JAMAIS empêcher la connexion : toute erreur est seulement notée dans les logs.
     */
    public static function check(User $user, string $ip): void
    {
        try {
            self::run($user, self::resolveIp($ip));
        } catch (\Throwable $e) {
            Log::error('Vérification de localisation échouée : ' . $e->getMessage());
        }
    }

    /**
     * En développement uniquement : permet de simuler une connexion depuis un autre pays
     * en mettant LOGIN_LOCATION_FAKE_IP=8.8.8.8 dans le fichier .env.
     */
    private static function resolveIp(string $ip): string
    {
        $fake = env('LOGIN_LOCATION_FAKE_IP');

        if ($fake && app()->environment('local')) {
            return $fake;
        }

        return $ip;
    }

    private static function run(User $user, string $ip): void
    {
        // 1. Où est cette IP ? (appel à un service externe)
        $position = Location::get($ip);

        if (!$position || !$position->countryName) {
            Log::info("Localisation impossible pour l'IP {$ip} (utilisateur {$user->email}).");
            return;
        }

        $country = $position->countryName;
        $city = $position->cityName;

        // 2. Dernier pays "accepté" pour cet utilisateur
        $lastKnown = DB::table('user_login_locations')
            ->where('user_id', $user->id)
            ->whereIn('status', ['known', 'confirmed'])
            ->orderByDesc('id')
            ->first();

        // Première connexion enregistrée : c'est notre point de départ
        if (!$lastKnown) {
            self::store($user, $ip, $country, $city, 'known');
            return;
        }

        // Même pays : on met juste la date à jour
        if ($lastKnown->country === $country) {
            DB::table('user_login_locations')
                ->where('id', $lastKnown->id)
                ->update(['updated_at' => now()]);
            return;
        }

        // 3. Pays différent. Déjà signalé et en attente de réponse ? Pas de 2e email.
        $alreadyPending = DB::table('user_login_locations')
            ->where('user_id', $user->id)
            ->where('country', $country)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return;
        }

        $id = self::store($user, $ip, $country, $city, 'pending');

        // 4. Email à l'utilisateur avec deux liens signés (valables 48 h)
        $prefix = Route::has('api.login-location.confirm') ? 'api.' : '';
        $confirmUrl = URL::temporarySignedRoute($prefix . 'login-location.confirm', now()->addHours(48), ['id' => $id]);
        $denyUrl = URL::temporarySignedRoute($prefix . 'login-location.deny', now()->addHours(48), ['id' => $id]);

        $place = $city ? "{$city}, {$country}" : $country;

        try {
            Mail::raw(
                "Bonjour,\n\n"
                . "Une connexion à votre compte Heroes CRM a eu lieu depuis un nouveau pays : {$place}.\n"
                . "Adresse IP : {$ip}\n"
                . 'Date : ' . now()->format('d/m/Y H:i') . "\n\n"
                . "C'était vous ? Cliquez ici :\n{$confirmUrl}\n\n"
                . "Ce n'était PAS vous ? Cliquez ici pour déconnecter immédiatement ce compte :\n{$denyUrl}\n",
                function ($mail) use ($user) {
                    $mail->to($user->email)->subject('Nouvelle connexion à votre compte : est-ce vous ?');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Email de localisation non envoyé à ' . $user->email . ' : ' . $e->getMessage());
        }

        // 5. Alerte aux admins
        AdminAlert::send(
            'Connexion depuis un nouveau pays',
            "Le compte {$user->email} s'est connecté depuis {$place} (IP {$ip}).\n"
            . "Dernier pays connu : {$lastKnown->country}.\n"
            . "L'utilisateur a reçu un email de confirmation."
        );
    }

    private static function store(User $user, string $ip, ?string $country, ?string $city, string $status): int
    {
        return DB::table('user_login_locations')->insertGetId([
            'user_id'    => $user->id,
            'ip'         => $ip,
            'country'    => $country,
            'city'       => $city,
            'status'     => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
