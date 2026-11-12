<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Stevebauman\Location\Facades\Location;

class LoginLocationCheck
{
    /**
     * À appeler après une authentification réussie (mot de passe, 2FA ou Google).
     *
     * Retourne true si la connexion doit être REFUSÉE : nouvelle ville pas encore confirmée
     * et blocage activé (config login_location.block). L'appelant doit alors déconnecter
     * l'utilisateur et répondre 403 avec le code "location_confirmation_required".
     *
     * En cas d'erreur technique (service de localisation en panne, etc.) on ne bloque pas :
     * l'erreur est seulement notée dans les logs.
     */
    public static function check(User $user, string $ip): bool
    {
        try {
            $isNewLocation = self::run($user, self::resolveIp($ip));
        } catch (\Throwable $e) {
            Log::error('Vérification de localisation échouée : ' . $e->getMessage());
            return false;
        }

        return $isNewLocation && (bool) config('login_location.block');
    }

    /**
     * Réponse JSON standard quand la connexion est refusée.
     */
    public static function blockedResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'code' => 'location_confirmation_required',
            'message' => 'Connexion depuis une nouvelle ville : un email de confirmation vous a été envoyé. '
                . 'Cliquez sur le lien « C\'était moi » puis reconnectez-vous.',
        ], 403);
    }

    /**
     * En développement uniquement : permet de simuler une connexion depuis une autre ville
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

    /**
     * @return bool true si cette connexion vient d'un nouvelle ville non confirmée
     */
    private static function run(User $user, string $ip): bool
    {
        // 1. Où est cette IP ? (appel à un service externe)
        $position = Location::get($ip);

        if (!$position || !$position->countryName) {
            Log::info("Localisation impossible pour l'IP {$ip} (utilisateur {$user->email}).");
            return false;
        }

        $country = $position->countryName;
        $city = $position->cityName;

        // 2. Lieux (ville + pays) déjà acceptés pour cet utilisateur
        $accepted = DB::table('user_login_locations')
            ->where('user_id', $user->id)
            ->whereIn('status', ['known', 'confirmed'])
            ->orderByDesc('id')
            ->get();

        // Première connexion enregistrée : c'est notre point de départ
        if ($accepted->isEmpty()) {
            self::store($user, $ip, $country, $city, 'known');
            return false;
        }

        // Lieu déjà accepté : on met juste la date à jour
        $match = $accepted->first(fn ($row) => self::samePlace($row, $country, $city));
        if ($match) {
            DB::table('user_login_locations')
                ->where('id', $match->id)
                ->update(['updated_at' => now()]);
            return false;
        }

        $lastKnown = $accepted->first();

        // 3. Nouvelle ville. Déjà signalée et en attente de réponse ?
        $ttl = max(1, (int) config('login_location.link_ttl_minutes'));

        $samePending = fn () => DB::table('user_login_locations')
            ->where('user_id', $user->id)
            ->where('country', $country)
            ->where('status', 'pending')
            ->when($city, fn ($q) => $q->where('city', $city), fn ($q) => $q->whereNull('city'));

        $pending = $samePending()->orderByDesc('id')->first();

        if ($pending) {
            if (Carbon::parse($pending->created_at)->gt(now()->subMinutes($ttl))) {
                // Email déjà envoyé et lien encore valable : pas de 2e email, mais la connexion reste en attente
                return true;
            }

            // Lien expiré : on le ferme et on en envoie un nouveau
            $samePending()->update(['status' => 'expired', 'updated_at' => now()]);
        }

        $id = self::store($user, $ip, $country, $city, 'pending');

        // 4. Email à l'utilisateur avec deux liens signés (durée : login_location.link_ttl_minutes)
        $expiresAt = now()->addMinutes($ttl);
        $prefix = Route::has('api.login-location.confirm') ? 'api.' : '';
        $confirmUrl = URL::temporarySignedRoute($prefix . 'login-location.confirm', $expiresAt, ['id' => $id]);
        $denyUrl = URL::temporarySignedRoute($prefix . 'login-location.deny', $expiresAt, ['id' => $id]);

        $place = $city ? "{$city}, {$country}" : $country;

        try {
            Mail::raw(
                "Bonjour,\n\n"
                . "Une connexion à votre compte Heroes CRM a eu lieu depuis une nouvelle ville : {$place}.\n"
                . "Adresse IP : {$ip}\n"
                . 'Date : ' . now()->format('d/m/Y H:i') . "\n\n"
                . (config('login_location.block')
                    ? "Cette connexion est bloquée tant que vous ne l'avez pas confirmée.\n"
                    : '')
                . "Les liens ci-dessous sont valables {$ttl} minutes.\n\n"
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
            'Connexion depuis une nouvelle ville',
            "Le compte {$user->email} s'est connecté depuis {$place} (IP {$ip}).\n"
            . "Dernier lieu connu : " . ($lastKnown->city ? "{$lastKnown->city}, " : '') . "{$lastKnown->country}.\n"
            . "L'utilisateur a reçu un email de confirmation."
        );

        return true;
    }

    /**
     * Même pays et même ville (sans tenir compte des majuscules).
     * Si une des deux villes est inconnue, on se rabat sur le pays seul.
     */
    private static function samePlace(object $row, string $country, ?string $city): bool
    {
        if ($row->country !== $country) {
            return false;
        }

        if (!$row->city || !$city) {
            return true;
        }

        return mb_strtolower($row->city) === mb_strtolower($city);
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
