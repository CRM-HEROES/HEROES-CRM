<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Who is in the project and with which roles, and what the roles allow. Read only.
 *
 * No e-mail, no phone number, no address: names and roles are enough to organise a team. The list of users is the one the
 * owner of the agent sees in the interface (a super administrator the owner does not work with is hidden); the roles and
 * their members need the right to manage the roles of users, the permissions of a role the right to update roles.
 */
class TeamMembersTool implements AgentToolInterface
{
    public const VIEWS = ['users', 'roles'];

    protected const MAX_PERMISSIONS = 400;

    public function key(): string
    {
        return 'team_members';
    }

    public function description(): string
    {
        return "Équipe du projet. view=users : les utilisateurs du projet (nom, rôles), filtrables par nom. "
            . "view=roles : les rôles du projet avec leur nombre de membres ; avec role_id, les membres du rôle et ses permissions (« all.project.role.update »…). "
            . "Ne donne ni e-mail ni téléphone. Les identifiants servent à user_roles_set et project_user_membership.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'view' => ['type' => 'string', 'enum' => self::VIEWS],
                'search' => ['type' => 'string', 'maxLength' => 100, 'description' => 'view=users : une partie du nom.'],
                'role_id' => ['type' => 'integer', 'description' => 'view=roles : le détail d\'un rôle.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'view=users : 30 par défaut.'],
            ],
            'required' => ['view'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'team';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        return $input['view'] === 'roles'
            ? $this->roles($context, (int) ($input['role_id'] ?? 0))
            : $this->users($context, $input);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function users(AgentExecutionContext $context, array $input): ToolResult
    {
        $project = $context->project;
        $query = $project->users()->forCurrentUser()->select('users.id', 'users.name', 'users.last_name');

        if (($search = trim((string) ($input['search'] ?? ''))) !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr($search, 0, 100)) . '%';
            $query->where(fn ($q) => $q->where('users.name', 'like', $like)->orWhere('users.last_name', 'like', $like));
        }

        $total = (clone $query)->count();
        $users = $query->orderBy('users.name')->limit(max(1, min(50, (int) ($input['limit'] ?? 30))))->get();

        $roles = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.project_id', $project->id)
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('model_has_roles.model_id', $users->pluck('id'))
            ->get(['model_has_roles.model_id as user_id', 'roles.id', 'roles.name'])
            ->groupBy('user_id');

        return ToolResult::ok(
            [
                'total' => $total,
                'users' => $users->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => trim($user->name . ' ' . $user->last_name),
                    'is_project_creator' => (int) $project->creator_id === (int) $user->id,
                    'roles' => ($roles[$user->id] ?? collect())->map(fn ($role) => ['id' => $role->id, 'name' => $role->name])->values()->all(),
                ])->all(),
            ],
            "Utilisateurs du projet",
            $users->count()
        );
    }

    protected function roles(AgentExecutionContext $context, int $roleId): ToolResult
    {
        $project = $context->project;

        if (!$context->owner->can('userRole', $project)) {
            return ToolResult::error("L'utilisateur propriétaire de l'agent n'a pas le droit de gérer les rôles des utilisateurs dans ce projet.", ToolResult::PERMISSION_DENIED);
        }

        if ($roleId <= 0) {
            $roles = Role::query()->where('project_id', $project->id)->orderBy('name')->get(['id', 'name']);
            $members = DB::table('model_has_roles')->where('project_id', $project->id)->where('model_type', User::class)
                ->selectRaw('role_id, count(*) as members')->groupBy('role_id')->pluck('members', 'role_id');

            return ToolResult::ok(
                ['roles' => $roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name, 'members' => (int) ($members[$role->id] ?? 0)])->all()],
                'Rôles du projet',
                $roles->count()
            );
        }

        $role = Role::query()->where('project_id', $project->id)->find($roleId);
        if (!$role) {
            return ToolResult::error("Rôle $roleId introuvable dans ce projet.", ToolResult::NOT_FOUND);
        }

        $data = [
            'role' => ['id' => $role->id, 'name' => $role->name],
            'members' => $role->users()->select('users.id', 'users.name', 'users.last_name')->limit(50)->get()
                ->map(fn ($user) => ['id' => $user->id, 'name' => trim($user->name . ' ' . $user->last_name)])->all(),
        ];

        // The permissions of a role: the same right as the screen that edits them
        if ($context->owner->can('projectRoleUpdate', $project)) {
            $names = $role->getPermissionNames();
            $data['permissions'] = $names->take(self::MAX_PERMISSIONS)->values()->all();
            $data['permissions_total'] = $names->count();
        } else {
            $data['permissions'] = null;
            $data['note'] = "Les permissions du rôle demandent le droit de modifier les rôles.";
        }

        return ToolResult::ok($data, "Rôle {$role->id}", 1);
    }
}
