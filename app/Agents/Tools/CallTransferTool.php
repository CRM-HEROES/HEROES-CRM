<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Calls\CallCompletion;
use App\Calls\WorkingHours;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Utils\ProjectSetting;

class CallTransferTool implements AgentToolInterface
{
    public function __construct(
        protected WorkingHours $workingHours,
        protected CallCompletion $completion,
    ) {
    }

    public function key(): string
    {
        return 'call_transfer';
    }

    public function description(): string
    {
        return 'Transfère un appel intéressé vers le conseiller disponible. Si personne n’est disponible, un rappel ouvré est créé et le conseiller est notifié.';
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false];
    }

    public function module(): string
    {
        return 'call';
    }

    public function risk(): string
    {
        return self::COMMUNICATION;
    }

    public function ability(): ?string
    {
        return 'prospectInteractionAdd';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $attempt = CallAttempt::query()->where('agent_run_id', $context->runId)
            ->where('agent_id', $context->agentId)
            ->where('project_id', $context->project->id)
            ->where('outcome', 'interested')
            ->latest('id')
            ->first();

        if (!$attempt) {
            return ToolResult::error('Aucun appel intéressé à transférer.', ToolResult::NOT_FOUND);
        }
        $project = Project::withoutGlobalScopes()->findOrFail($context->project->id);
        $prospect = Prospect::withoutGlobalScopes()->find($attempt->prospect_id);
        $agent = \App\Models\Agent::query()->find($attempt->agent_id);
        $owner = $agent ? User::query()->find($agent->user_id) : null;
        if (!$prospect || !$owner) {
            return ToolResult::error('Le prospect ou le propriétaire de l’agent est introuvable.', ToolResult::NOT_FOUND);
        }

        $settings = ProjectSetting::get($project, 'calls', []);
        $manualAvailable = (bool) (is_object($settings) ? ($settings->transfer_available ?? false) : ($settings['transfer_available'] ?? false));
        $adviser = $project->call_transfer_user_id
            ? User::query()->whereKey($project->call_transfer_user_id)->first()
            : null;
        $available = $manualAvailable && $adviser && $this->workingHours->isOpen($prospect);

        $reason = !$adviser
            ? 'Aucun conseiller de transfert n’est configuré.'
            : (!$available
                ? 'Aucun conseiller n’est indiqué comme disponible pendant les heures ouvrées.'
                : 'L’intégration CloudTalk actuelle ne fournit pas de commande de transfert d’appel; un rappel doit être organisé.');

        try {
            $this->completion->complete($attempt, $prospect, $project, $owner, [
                'external_id' => $attempt->external_id,
                'outcome' => 'callback',
                'summary' => $reason,
                'duration_seconds' => $attempt->duration_seconds,
                'immediate_callback' => true,
            ]);
        } catch (\RuntimeException $exception) {
            return ToolResult::error($exception->getMessage(), ToolResult::UNAVAILABLE);
        }

        return ToolResult::ok([
            'status' => CallAttempt::COMPLETED,
            'outcome' => 'callback',
            'adviser_id' => $adviser?->id,
        ], $reason . ' Le rappel est planifié au prochain créneau ouvré.', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return ['action' => 'Transférer l’appel intéressé au conseiller disponible ou planifier un rappel ouvré'];
    }
}
