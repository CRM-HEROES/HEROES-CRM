<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginAttemptLimiter;

class LockController extends Controller
{
    /**
     * Bloquer l'accès d'un utilisateur.
     */
    public function update(User $user)
    {
        $this->authorizeManage($user);

        $user->forceFill(['locked_at' => now()])->save();

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Débloquer l'accès d'un utilisateur (remet aussi le compteur d'échecs à zéro).
     */
    public function destroy(User $user)
    {
        $this->authorizeManage($user);

        LoginAttemptLimiter::unlock($user);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Super admin, créateur du compte, ou créateur d'un projet auquel l'utilisateur appartient.
     */
    private function authorizeManage(User $user): void
    {
        $me = auth()->user();

        abort_if($me->id == $user->id, 404);

        abort_unless(
            $me->is_super_admin
            || $user->creator_id == $me->id
            || $user->projects()->where('projects.creator_id', $me->id)->exists(),
            404
        );
    }
}
