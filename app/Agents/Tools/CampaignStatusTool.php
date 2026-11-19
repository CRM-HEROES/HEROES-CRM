<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Campaign;

/**
 * The historical campaigns of the project (rules, operators, actions): which exist, whether they are on, running or in
 * error, and what they do. The text of an action (an SMS, an e-mail) is never given: only its name and kind.
 */
class CampaignStatusTool implements AgentToolInterface
{
    public function key(): string
    {
        return 'campaign_status';
    }

    public function description(): string
    {
        return "Campagnes historiques du projet (règles, opérateurs, actions). view=list : les campagnes avec leur cible (prospect, order, event), leur fréquence, si elles sont activées, en attente, en cours ou en erreur. "
            . "view=detail : une campagne avec ses actions (nom et type, jamais le texte) et le nombre de règles et d'opérateurs. "
            . "Pour les automatisations récentes, utilise plutôt les workflows.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'view' => ['type' => 'string', 'enum' => ['list', 'detail']],
                'campaign_id' => ['type' => 'integer', 'description' => 'view=detail : la campagne.'],
                'active' => ['type' => 'boolean', 'description' => 'view=list : seulement les campagnes activées (true) ou désactivées (false).'],
                'in_error' => ['type' => 'boolean', 'description' => 'view=list : seulement celles dont la vérification des règles a échoué.'],
            ],
            'required' => ['view'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'campaign';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'projectCampaignView';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        if ($input['view'] === 'detail') {
            return $this->detail($context, (int) ($input['campaign_id'] ?? 0));
        }

        $query = Campaign::query()->where('project_id', $context->project->id);
        isset($input['active']) && $query->where('active', (bool) $input['active']);
        isset($input['in_error']) && $query->where('error', (bool) $input['in_error']);

        $campaigns = $query->orderByDesc('id')->limit(50)->get();

        return ToolResult::ok(
            ['campaigns' => $campaigns->map(fn (Campaign $campaign) => $this->summary($campaign))->all()],
            'Campagnes du projet',
            $campaigns->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function detail(AgentExecutionContext $context, int $id): ToolResult
    {
        $campaign = $id > 0 ? Campaign::query()->where('project_id', $context->project->id)->find($id) : null;

        if (!$campaign) {
            return ToolResult::error("Campagne $id introuvable dans ce projet.", ToolResult::NOT_FOUND);
        }

        $actions = $campaign->actions()->get()->map(fn ($action) => [
            'name' => mb_substr(strip_tags((string) $action->name), 0, 120),
            'type' => $action->action,
        ])->all();

        return ToolResult::ok(
            $this->summary($campaign) + [
                'actions' => $actions,
                'rules' => $campaign->rules()->count(),
                'operators' => $campaign->operators()->count(),
                're_process_processed_prospects' => (bool) $campaign->re_process_processed_prospects,
            ],
            "Campagne {$campaign->id}",
            1
        );
    }

    protected function summary(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'name' => mb_substr(strip_tags((string) $campaign->name), 0, 120),
            'for' => $campaign->for,
            'frequency' => $campaign->frequency,
            'date' => $campaign->date,
            'active' => (bool) $campaign->active,
            'pending' => (bool) $campaign->pending,
            'processing' => (bool) $campaign->processing,
            'error' => (bool) $campaign->error,
        ];
    }
}
