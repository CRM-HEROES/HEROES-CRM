<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /**
     * Activation du compte : l'utilisateur choisit son mot de passe.
     * Route publique mais signée (middleware "signed") : seule une invitation envoyée par le CRM passe.
     */
    public function accept(Request $request, User $user)
    {
        // Lien périmé, déjà utilisé, ou remplacé par une invitation plus récente
        $valid = $user->must_change_password
            && $user->password_temporary_expires_at
            && $user->password_temporary_expires_at->isFuture()
            && hash_equals(UserInvitation::version($user), (string) $request->query('v'));

        if (!$valid) {
            return response()->json([
                'code' => 'invitation_invalid',
                'message' => "Ce lien n'est plus valable (expiré ou déjà utilisé). Demandez une nouvelle invitation à votre administrateur.",
            ], 410);
        }

        $request->validate([
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ], [
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if (mb_strtolower($request->input('password')) === mb_strtolower($user->email)) {
            return response()->json([
                'message' => "Le mot de passe ne doit pas être identique à votre adresse email.",
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
            'password_temporary_expires_at' => null,
        ])->save();

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Votre mot de passe est enregistré. Vous pouvez vous connecter.',
        ]);
    }
}
