<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoginLocationController extends Controller
{
    /**
     * true : "ce n'était pas moi" bloque aussi le compte (un admin doit le débloquer).
     * false : on déconnecte seulement.
     */
    const BLOCK_ON_DENY = false;

    // ---- Liens de l'email (signés, sans connexion nécessaire) ----

    public function confirm($id)
    {
        $row = DB::table('user_login_locations')->where('id', $id)->where('status', 'pending')->first();

        if (!$row) {
            return $this->page('⚠️', 'Lien non valable', 'Ce lien a déjà été utilisé ou n\'est plus valable.', 410);
        }

        DB::table('user_login_locations')->where('id', $id)->update([
            'status' => 'confirmed',
            'updated_at' => now(),
        ]);

        return $this->page('✅', 'Merci !', 'Ce pays est maintenant enregistré comme habituel pour votre compte.');
    }

    public function deny($id)
    {
        $row = DB::table('user_login_locations')->where('id', $id)->where('status', 'pending')->first();

        if (!$row) {
            return $this->page('⚠️', 'Lien non valable', 'Ce lien a déjà été utilisé ou n\'est plus valable.', 410);
        }

        $this->applyDeny($row);

        return $this->page(
            '🔒',
            'Compte sécurisé',
            'Votre compte a été déconnecté partout et les administrateurs ont été prévenus. Changez votre mot de passe dès que possible.'
        );
    }

    // ---- Depuis le CRM (utilisateur connecté) ----

    /**
     * Connexions en attente de réponse pour l'utilisateur connecté.
     */
    public function pending()
    {
        $rows = DB::table('user_login_locations')
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subHours(48))
            ->orderByDesc('id')
            ->get(['id', 'country', 'city', 'ip', 'created_at']);

        return response()->json($rows);
    }

    /**
     * "Ce n'était pas moi" cliqué dans le CRM.
     */
    public function report($id)
    {
        // On ne peut signaler que SES propres connexions
        $row = DB::table('user_login_locations')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        abort_unless($row, 404);

        $this->applyDeny($row);

        return response()->json(['message' => 'Compte déconnecté partout.']);
    }

    // ---- Logique commune ----

    private function applyDeny($row): void
    {
        $user = User::find($row->user_id);

        if ($user) {
            // Déconnecte les applications (jetons API)
            $user->tokens()->delete();

            // Déconnecte les sessions du navigateur, si elles sont stockées en base
            if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            if (self::BLOCK_ON_DENY) {
                $user->forceFill(['banned_at' => now()])->save();
            }

            AdminAlert::send(
                'Connexion suspecte signalée par l\'utilisateur',
                "{$user->email} indique que la connexion depuis {$row->country} (IP {$row->ip}) n'était PAS la sienne.\n"
                . 'Le compte a été déconnecté partout'
                . (self::BLOCK_ON_DENY ? ' et bloqué.' : '.') . "\n"
                . 'Il est conseillé de lui faire changer son mot de passe.'
            );
        }

        DB::table('user_login_locations')->where('id', $row->id)->update([
            'status' => 'denied',
            'updated_at' => now(),
        ]);
    }

    private function page(string $icon, string $title, string $message, int $status = 200)
    {
        return response()->view('login-location.result', compact('icon', 'title', 'message'), $status);
    }
}
