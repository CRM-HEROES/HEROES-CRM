<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\LoginAttemptLimiter;

class LockController extends Controller
{
    public function update(Project $project, User $user)
    {
        $this->authorizeManage($project, $user);

        $user->forceFill(['locked_at' => now()])->save();

        return ['message' => trans('common.success.attached_resource')];
    }

    public function destroy(Project $project, User $user)
    {
        $this->authorizeManage($project, $user);

        LoginAttemptLimiter::unlock($user);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Admin du projet (même règle que l'affichage d'un utilisateur du projet), jamais sur soi-même.
     */
    private function authorizeManage(Project $project, User $user): void
    {
        abort_unless(auth()->user()->can('', $project), 404);
        abort_unless($project->users()->find($user->id), 404);
        abort_if(auth()->id() == $user->id, 404);
    }
}
