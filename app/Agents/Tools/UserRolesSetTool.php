<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\Role;
use App\Models\User;

/**
 * Gives or takes away roles of users of the project. A role carries rights: this is always decided by a human, even
 * when the agent is otherwise autonomous.
 *
 * Limits that do not depend on the screen: at most 10 users and 5 roles at once, only users and roles of this project,
 * never a super administrator, the creator of the project or the owner of the agent, and a role is only given if the
 * owner holds every permission it carries (unless he administers the project). The routes of the CRM check the right
 * "userRole" themselves.
 */
class UserRolesSetTool implements AgentToolInterface, ChecksBeforeApproval
{
    public const MAX_USERS = 10;
    public const MAX_ROLES = 5;

    public function __construct(protected InternalApi $api, protected TeamAccess $access)
    {
    }

    public function key(): string
    {
        return 'user_roles_set';
    }

    public function description(): string
    {
        return "Donne (action=assign) ou retire (action=remove) des rôles à des utilisateurs du projet : user_ids (" . self::MAX_USERS . " au plus) et role_ids (" . self::MAX_ROLES . " au plus), "
            . "identifiants donnés par team_members. Un rôle porte des droits : l'utilisateur valide toujours. "
            . "Refusé pour un super-administrateur, le créateur du projet et le propriétaire de l'agent, et pour un rôle qui porte des permissions que le propriétaire n'a pas lui-même.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => ['type' => 'string', 'enum' => ['assign', 'remove']],
                'user_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'minItems' => 1, 'maxItems' => self::MAX_USERS],
                'role_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'minItems' => 1, 'maxItems' => self::MAX_ROLES],
            ],
            'required' => ['action', 'user_ids', 'role_ids'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'team';
    }

    public function risk(): string
    {
        return self::DESTRUCTIVE;
    }

    public function ability(): ?string
    {
        return 'userRole';
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $plan = $this->plan($context, $input);

        return $plan instanceof ToolResult ? $plan : null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $plan = $this->plan($context, $input);
        if ($plan instanceof ToolResult) {
            return $plan;
        }

        $method = $input['action'] === 'assign' ? 'PUT' : 'DELETE';
        $applied = 0;
        $failed = [];

        foreach ($plan['users'] as $user) {
            foreach ($plan['roles'] as $role) {
                $response = $this->api->call($context, $method, "user/{$user->id}/role/{$role->id}");

                if ($response->ok()) {
                    $applied++;
                } else {
                    $failed[] = ['user_id' => $user->id, 'role_id' => $role->id, 'error' => $response->toError()->data['error'] ?? 'Refusé par le CRM.'];
                }
            }
        }

        if (!$applied) {
            return ToolResult::error('Aucune modification appliquée : ' . ($failed[0]['error'] ?? 'refusé par le CRM.'), ToolResult::VALIDATION_FAILED);
        }

        return ToolResult::ok(
            ['action' => $input['action'], 'applied' => $applied, 'failed' => $failed],
            "$applied rôle(s) " . ($input['action'] === 'assign' ? 'donné(s)' : 'retiré(s)'),
            $applied
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $users = User::query()->whereIn('id', array_map('intval', (array) ($input['user_ids'] ?? [])))->get(['id', 'name', 'last_name']);
        $roles = Role::query()->where('project_id', $context->project->id)->whereIn('id', array_map('intval', (array) ($input['role_ids'] ?? [])))->get(['id', 'name']);

        return [
            'action' => ($input['action'] ?? '') === 'assign' ? 'Donner des rôles' : 'Retirer des rôles',
            'utilisateurs' => $users->map(fn ($u) => trim($u->name . ' ' . $u->last_name))->all(),
            'rôles' => $roles->pluck('name')->all(),
        ];
    }

    /**
     * Everything checked before the first call: [users => User[], roles => Role[]], or the error that stops it all.
     *
     * @return array{users: array, roles: array}|ToolResult
     */
    protected function plan(AgentExecutionContext $context, array $input): array|ToolResult
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', (array) $input['user_ids']), fn ($id) => $id > 0)));
        $roleIds = array_values(array_unique(array_filter(array_map('intval', (array) $input['role_ids']), fn ($id) => $id > 0)));

        if (!$userIds || !$roleIds) {
            return ToolResult::error('Il faut au moins un utilisateur et un rôle.', ToolResult::VALIDATION_FAILED);
        }
        if (count($userIds) > self::MAX_USERS || count($roleIds) > self::MAX_ROLES) {
            return ToolResult::error('Au plus ' . self::MAX_USERS . ' utilisateurs et ' . self::MAX_ROLES . ' rôles à la fois.', ToolResult::LIMIT_EXCEEDED);
        }

        $project = $context->project;
        $roles = Role::query()->where('project_id', $project->id)->whereIn('id', $roleIds)->get();
        if ($roles->count() !== count($roleIds)) {
            return ToolResult::error('Un des rôles est inconnu dans ce projet (team_members donne les identifiants).', ToolResult::NOT_FOUND);
        }

        $users = $project->users()->whereIn('users.id', $userIds)->get();
        if ($users->count() !== count($userIds)) {
            return ToolResult::error("Un des utilisateurs n'est pas membre de ce projet (team_members donne les identifiants).", ToolResult::NOT_FOUND);
        }

        foreach ($users as $user) {
            if ($reason = $this->access->protectedReason($context, $user)) {
                return ToolResult::error($reason, ToolResult::PERMISSION_DENIED);
            }
        }

        if ($input['action'] === 'assign') {
            foreach ($roles as $role) {
                if ($beyond = $this->access->permissionsBeyondOwner($context, $role)) {
                    return ToolResult::error(
                        "Le rôle « {$role->name} » porte des permissions que le propriétaire de l'agent n'a pas lui-même (par exemple « {$beyond[0]} ») : il ne peut pas le donner.",
                        ToolResult::PERMISSION_DENIED
                    );
                }
            }
        }

        return ['users' => $users->all(), 'roles' => $roles->all()];
    }
}
