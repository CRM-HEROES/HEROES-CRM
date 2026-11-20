<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\Campaign;

/**
 * Turns a campaign on or off, as the "Activé" switch of the page does. An active campaign runs on its own at each
 * pass of the scheduler and its actions can send messages: a human always decides.
 */
class CampaignSetActiveTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'campaign_set_active';
    }

    public function description(): string
    {
        return "Active ou désactive une campagne historique du projet. Activer une campagne peut envoyer des messages ou modifier des prospects à chaque passage du planificateur : toujours validé par l'utilisateur. "
            . "Avant d'activer, lis la campagne avec campaign_status (actions, règles, erreur) et dis ce qu'elle fera.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'campaign_id' => ['type' => 'integer'],
                'active' => ['type' => 'boolean'],
            ],
            'required' => ['campaign_id', 'active'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'campaign';
    }

    public function risk(): string
    {
        return self::COMMUNICATION;
    }

    public function ability(): ?string
    {
        return 'projectCampaignUpdate';
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        return $this->problem($this->campaign($context, $input), $input);
    }

    protected function campaign(AgentExecutionContext $context, array $input): ?Campaign
    {
        return Campaign::query()->where('project_id', $context->project->id)->find((int) $input['campaign_id']);
    }

    protected function problem(?Campaign $campaign, array $input): ?ToolResult
    {
        if (!$campaign) {
            return ToolResult::error("Campagne {$input['campaign_id']} introuvable dans ce projet.", ToolResult::NOT_FOUND);
        }
        if ($input['active'] && $campaign->error) {
            return ToolResult::error('Les règles de cette campagne sont en erreur : corrige-les dans la page des campagnes (« Vérifier les règles ») avant de l\'activer.', ToolResult::INVALID_INPUT);
        }

        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $campaign = $this->campaign($context, $input);

        if ($error = $this->problem($campaign, $input)) {
            return $error;
        }

        $response = $this->api->call($context, 'PUT', "campaign/{$campaign->id}", ['active' => (bool) $input['active']]);

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(
            ['campaign_id' => $campaign->id, 'active' => (bool) $input['active']],
            'Campagne ' . ($input['active'] ? 'activée' : 'désactivée'),
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $name = Campaign::query()->where('project_id', $context->project->id)->whereKey((int) ($input['campaign_id'] ?? 0))->value('name');

        return [
            'action' => !empty($input['active']) ? 'Activer la campagne' : 'Désactiver la campagne',
            'campaign' => $name ? mb_substr(strip_tags($name), 0, 120) : '#' . ($input['campaign_id'] ?? '?'),
        ];
    }
}
