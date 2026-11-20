<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\User;

/**
 * Brings an existing user into the project, or takes one out. Never creates an account (that is an invitation, which an
 * agent does not send). Always decided by a human: it opens or closes access to the data of the project.
 *
 * The routes of the CRM only let a super administrator or an administrator of the project do this: the tool asks the
 * same of the owner of the agent. At most 10 at once; never a super administrator, the creator of the project or the owner.
 */
class ProjectUserMembershipTool implements AgentToolInterface, ChecksBeforeApproval
{
    public const MAX = 10;

    public function __construct(protected InternalApi $api, protected TeamAccess $access)
    {
    }

    public function key(): string
    {
        return 'project_user_membership';
    }

    public function description(): string
    {
        return "Rattache un compte existant au projet (action=attach, emails = les adresses que l'utilisateur t'a données) ou retire des utilisateurs du projet (action=detach, user_ids de team_members). "
            . self::MAX . " au plus. Ne crée aucun compte et n'envoie aucune invitation. L'utilisateur valide toujours. "
            . "Retirer un utilisateur du projet ne supprime pas son compte. Refusé pour un super-administrateur, le créateur du projet et le propriétaire de l'agent.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => ['type' => 'string', 'enum' => ['attach', 'detach']],
                'emails' => ['type' => 'array', 'items' => ['type' => 'string', 'maxLength' => 190], 'maxItems' => self::MAX, 'description' => 'action=attach : adresses e-mail de comptes existants.'],
                'user_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => self::MAX, 'description' => 'action=detach : utilisateurs du projet.'],
            ],
            'required' => ['action'],
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
        return null;
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $plan = $this->plan($context, $input);

        return $plan instanceof ToolResult ? $plan : null;
    }

    /**
     * The calls to make, checked beforehand, or the refusal.
     *
     * @return array<int, array>|ToolResult
     */
    protected function plan(AgentExecutionContext $context, array $input): array|ToolResult
    {
        if (!$this->access->isAdministrator($context)) {
            return ToolResult::error("Seul un administrateur du projet (ou un super-administrateur) peut rattacher ou retirer des utilisateurs : ce n'est pas le cas du propriétaire de l'agent.", ToolResult::PERMISSION_DENIED);
        }

        return $input['action'] === 'attach' ? $this->attachPlan($context, $input) : $this->detachPlan($context, $input);
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $plan = $this->plan($context, $input);
        if ($plan instanceof ToolResult) {
            return $plan;
        }

        $applied = 0;
        $failed = [];
        foreach ($plan as [$label, $method, $path, $body]) {
            $response = $this->api->call($context, $method, $path, $body);

            if ($response->ok()) {
                $applied++;
            } else {
                $failed[] = ['what' => $label, 'error' => $response->toError()->data['error'] ?? 'Refusé par le CRM.'];
            }
        }

        if (!$applied) {
            return ToolResult::error('Aucune modification appliquée : ' . ($failed[0]['error'] ?? 'refusé par le CRM.'), ToolResult::VALIDATION_FAILED);
        }

        return ToolResult::ok(
            ['action' => $input['action'], 'applied' => $applied, 'failed' => $failed],
            "$applied utilisateur(s) " . ($input['action'] === 'attach' ? 'rattaché(s)' : 'retiré(s)') . ' du projet',
            $applied
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [
            'action' => ($input['action'] ?? '') === 'attach' ? 'Rattacher des comptes au projet' : 'Retirer des utilisateurs du projet',
            'nombre' => count((array) (($input['action'] ?? '') === 'attach' ? ($input['emails'] ?? []) : ($input['user_ids'] ?? []))),
        ];
    }

    protected function attachPlan(AgentExecutionContext $context, array $input): array|ToolResult
    {
        $emails = array_values(array_unique(array_filter(array_map(fn ($e) => mb_strtolower(trim((string) $e)), (array) ($input['emails'] ?? [])))));

        if (!$emails) {
            return ToolResult::error('action=attach demande au moins une adresse e-mail.', ToolResult::VALIDATION_FAILED);
        }
        if (count($emails) > self::MAX) {
            return ToolResult::error('Au plus ' . self::MAX . ' comptes à la fois.', ToolResult::LIMIT_EXCEEDED);
        }

        $plan = [];
        foreach ($emails as $email) {
            $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();

            if (!$user) {
                return ToolResult::error("Aucun compte avec l'adresse « $email » : l'invitation d'un nouvel utilisateur se fait dans le CRM.", ToolResult::NOT_FOUND);
            }
            if ($reason = $this->access->protectedReason($context, $user)) {
                return ToolResult::error($reason, ToolResult::PERMISSION_DENIED);
            }
            if ($context->project->users()->where('users.id', $user->id)->exists()) {
                return ToolResult::error("« $email » est déjà membre du projet.", ToolResult::INVALID_INPUT);
            }

            $plan[] = ["rattacher $email", 'POST', 'user', ['email' => $email]];
        }

        return $plan;
    }

    protected function detachPlan(AgentExecutionContext $context, array $input): array|ToolResult
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($input['user_ids'] ?? [])), fn ($id) => $id > 0)));

        if (!$ids) {
            return ToolResult::error('action=detach demande au moins un identifiant (team_members).', ToolResult::VALIDATION_FAILED);
        }
        if (count($ids) > self::MAX) {
            return ToolResult::error('Au plus ' . self::MAX . ' utilisateurs à la fois.', ToolResult::LIMIT_EXCEEDED);
        }

        $users = $context->project->users()->whereIn('users.id', $ids)->get();
        if ($users->count() !== count($ids)) {
            return ToolResult::error("Un des utilisateurs n'est pas membre de ce projet (team_members donne les identifiants).", ToolResult::NOT_FOUND);
        }

        $plan = [];
        foreach ($users as $user) {
            if ($reason = $this->access->protectedReason($context, $user)) {
                return ToolResult::error($reason, ToolResult::PERMISSION_DENIED);
            }
            $plan[] = ["retirer l'utilisateur {$user->id}", 'DELETE', "user/{$user->id}", []];
        }

        return $plan;
    }
}
