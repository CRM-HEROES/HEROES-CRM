<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function change(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Le nouveau mot de passe doit être différent du mot de passe actuel.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_temporary_expires_at' => null,
        ]);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Votre mot de passe a été modifié avec succès.',
        ]);
    }
}
