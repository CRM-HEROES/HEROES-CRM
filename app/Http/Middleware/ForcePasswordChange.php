<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        if (
            !$user->password_temporary_expires_at ||
            $user->password_temporary_expires_at->isPast()
        ) {
            $user->tokens()->delete();

            return response()->json([
                'code' => 'password_expired',
                'message' => 'Votre mot de passe temporaire a expiré. Veuillez contacter l’administrateur.',
            ], 403);
        }

        $isAllowed = (
            ($request->isMethod('GET') && $request->path() === 'api/auth') ||
            ($request->isMethod('POST') && $request->path() === 'api/password/change')
        );

        if (!$isAllowed) {
            return response()->json([
                'code' => 'password_change_required',
                'message' => 'Vous devez choisir un nouveau mot de passe pour continuer.',
            ], 403);
        }

        return $next($request);
    }
}
