<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Role;
use App\Models\User;
use App\Policies\ProjectPolicy;

/**
 * What the tools that change who is in the project and with which roles share: who counts as an administrator of the
 * project, which users are never touched by an agent, and the rule that nobody gives a right he does not hold.
 */
class TeamAccess
{
    /**
     * A super administrator, the creator of the project, or someone who holds every permission of the project.
     */
    public function isAdministrator(AgentExecutionContext $context): bool
    {
        $owner = $context->owner;

        return $owner->is_super_admin
            || (int) $context->project->creator_id === (int) $owner->id
            || $owner->can('', $context->project);
    }

    /**
     * Why this user must not be changed by an agent, or null: a super administrator, the creator of the project, and the
     * owner of the agent himself (an agent does not change the rights of the person it works for).
     */
    public function protectedReason(AgentExecutionContext $context, User $user): ?string
    {
        if ($user->is_super_admin) {
            return "Cet utilisateur est super-administrateur : un agent n'y touche pas.";
        }
        if ((int) $context->project->creator_id === (int) $user->id) {
            return "Cet utilisateur est le créateur du projet : un agent n'y touche pas.";
        }
        if ((int) $context->owner->id === (int) $user->id) {
            return "Un agent ne modifie pas l'accès ni les rôles de son propre propriétaire.";
        }

        return null;
    }

    /**
     * The permissions of this role the owner does not hold himself (none for an administrator).
     *
     * @return string[]
     */
    public function permissionsBeyondOwner(AgentExecutionContext $context, Role $role): array
    {
        if ($this->isAdministrator($context)) {
            return [];
        }

        $policy = app(ProjectPolicy::class);

        return $role->getPermissionNames()
            ->reject(fn ($permission) => $policy->can($context->owner, $permission))
            ->values()->all();
    }
}
