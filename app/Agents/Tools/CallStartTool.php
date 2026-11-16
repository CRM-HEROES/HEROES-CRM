<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Calls\CallEligibility;
use App\Calls\CallCompletion;
use App\Calls\Voice\VoiceProvider;
use App\Calls\CallSettings;
use App\Models\AgentDocument;
use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\AgentRun;
use App\Models\Interaction;
use App\Models\Prospect;
use App\Support\PhoneNumber;
use RuntimeException;

/**
 * Prepares and starts a validated, human-approved call.
 */
class CallStartTool implements AgentToolInterface
{
    public function __construct(
        protected ReadGateway $reader,
        protected CallEligibility $eligibility,
        protected VoiceProvider $provider,
        protected CallCompletion $completion,
    ) {
    }

    public function key(): string
    {
        return 'call_start';
    }

    public function description(): string
    {
        return "Prépare un appel à un seul prospect visible après vérification du consentement, du téléphone, des heures de travail et des limites. L'appel attend toujours la validation humaine.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'prospect_id' => ['type' => 'integer', 'minimum' => 1],
            ],
            'required' => ['prospect_id'],
            'additionalProperties' => false,
        ];
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
        $prospect = $this->visibleProspect($context, (int) $input['prospect_id']);

        if (!$prospect) {
            return ToolResult::error('Prospect introuvable ou non accessible dans ce projet.', ToolResult::NOT_FOUND);
        }

        $attempt = ($context->runId ? CallAttempt::query()
            ->where('agent_run_id', $context->runId)
            ->where('prospect_id', $prospect->id)
            ->first() : null)
            ?? $this->eligibility->prepare($prospect, Agent::findOrFail($context->agentId), $context->project, $context->runId);
        $reason = $this->eligibility->reason($prospect, $context->project);
        if ($reason === 'outside_calling_hours') {
            $attempt->update([
                'status' => CallAttempt::SKIPPED,
                'skip_reason' => $reason,
                'scheduled_at' => null,
            ]);

            return ToolResult::error(
                'Appel non lancé [' . $reason . '] : ' . $this->eligibility->callMessage($reason, $prospect),
                ToolResult::VALIDATION_FAILED
            );
        }
        if ($reason) {
            $attempt->update(['status' => CallAttempt::SKIPPED, 'skip_reason' => $reason, 'scheduled_at' => null]);
            return ToolResult::error(
                'Appel non lancé [' . $reason . '] : ' . $this->eligibility->callMessage($reason, $prospect),
                ToolResult::VALIDATION_FAILED
            );
        }
        if ($attempt->status !== CallAttempt::QUEUED) {
            return ToolResult::error('Appel non lancé : cette tentative n’est plus en attente.', ToolResult::VALIDATION_FAILED);
        }

        $attempt->update(['status' => CallAttempt::CALLING, 'started_at' => now()]);
        try {
            $briefing = $this->briefing($context, $prospect);
            $result = $this->provider->start($attempt, $prospect, $briefing);
        } catch (RuntimeException $exception) {
            $attempt->update([
                'status' => CallAttempt::FAILED,
                'skip_reason' => $exception->getMessage(),
                'ended_at' => now(),
            ]);

            return ToolResult::error('Appel non lancé : ' . $exception->getMessage(), ToolResult::UNAVAILABLE);
        }

        if (($result['outcome'] ?? null) === 'pending') {
            $attempt->update([
                'external_id' => $result['external_id'] ?? null,
                'summary' => $result['summary'] ?? null,
            ]);

            return ToolResult::ok([
                'status' => CallAttempt::CALLING,
                'call_id' => $attempt->external_id,
                'briefing' => $this->briefing($context, $prospect),
            ], 'Appel lancé par ' . $this->providerLabel($context) . ' ; l’issue sera enregistrée après le webhook.', 1);
        }

        $this->completion->complete($attempt, $prospect, $context->project, $context->owner, $result);
        $attempt->refresh();

        return ToolResult::ok([
            'status' => $attempt->status,
            'outcome' => $result['outcome'],
            'call_id' => $attempt->external_id,
        ], 'Appel terminé pour le prospect #' . $prospect->id, 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $prospect = $this->visibleProspect($context, (int) ($input['prospect_id'] ?? 0));

        if (!$prospect) {
            return ['error' => 'Prospect introuvable ou non accessible dans ce projet.'];
        }

        $name = trim(implode(' ', array_filter([$prospect->first_name, $prospect->last_name])));
        $attempt = $this->eligibility->prepare($prospect, Agent::findOrFail($context->agentId), $context->project, $context->runId);

        return [
            'action' => 'Préparer un appel',
            'prospect' => ($name !== '' ? $name . ' ' : '') . '#' . $prospect->id,
            'numero' => $this->maskedPhone($prospect->mobile_phone_number ?: $prospect->phone_number) ?? 'aucun numéro',
            'status' => $attempt->status,
            'reason' => $attempt->skip_reason,
            'scheduled_at' => $attempt->scheduled_at?->toIso8601String(),
            'fournisseur' => $this->providerLabel($context),
            'briefing' => $this->briefing($context, $prospect),
        ];
    }

    protected function providerLabel(AgentExecutionContext $context): string
    {
        $provider = CallSettings::provider($context->project);
        if ($provider !== 'cloudtalk') {
            return match ($provider) {
                'fake' => 'Simulation (aucun appel réel)',
                'ai_voice' => 'Voix IA (squelette non connecté)',
                default => $provider,
            };
        }

        $line = \App\Models\Line::query()
            ->where('project_id', $context->project->id)
            ->where('operator', 'cloudtalk')
            ->where('user_id', $context->owner->id)
            ->first();

        return $line
            ? 'Cloudtalk - ' . $line->name
            : 'CloudTalk (ligne non configurée)';
    }

    protected function briefing(AgentExecutionContext $context, Prospect $prospect): array
    {
        $documents = AgentDocument::query()
            ->where('agent_id', $context->agentId)
            ->where('status', 'ready')
            ->whereHas('agent', fn ($query) => $query->where('user_id', $context->owner->id))
            ->get(['name', 'summary'])
            ->map(fn (AgentDocument $document) => ['document' => $document->name, 'summary' => $document->summary])
            ->all();

        $latestMessage = $context->runId
            ? AgentRun::query()
                ->whereKey($context->runId)
                ->where('user_id', $context->owner->id)
                ->where('agent_id', $context->agentId)
                ->with(['conversation' => fn ($query) => $query->with([
                    'messages' => fn ($messages) => $messages->where('role', 'user')
                        ->whereHas('attachments')
                        ->with('attachments')
                        ->orderByDesc('id')
                        ->limit(1),
                ])])
                ->first()?->conversation?->messages?->first()
            : null;
        $chatAttachments = $latestMessage?->attachments
            ->map(fn ($attachment) => [
                'document' => $attachment->name,
                'summary' => $attachment->summary,
            ])
            ->all() ?? [];
        $referenceSummaries = array_merge($documents, $chatAttachments);

        $history = Interaction::query()
            ->where('prospect_id', $prospect->id)
            ->latest('started_at')
            ->limit(5)
            ->get(['status', 'data', 'started_at'])
            ->map(fn (Interaction $interaction) => [
                'at' => $interaction->started_at?->toIso8601String(),
                'status' => $interaction->status,
                'summary' => mb_substr((string) data_get($interaction->data, 'note', data_get($interaction->data, 'summary', '')), 0, 300),
            ])
            ->all();

        return [
            'prospect' => trim(implode(' ', array_filter([$prospect->first_name, $prospect->last_name]))),
            'telephone' => $this->maskedPhone($prospect->mobile_phone_number ?: $prospect->phone_number),
            'historique' => $history,
            'documents' => $documents,
            'pieces_jointes' => $chatAttachments,
            'points_a_dire' => array_values(array_merge(...array_map(
                fn ($document) => array_merge(
                    (array) data_get($document, 'summary.services', []),
                    (array) data_get($document, 'summary.arguments', []),
                    (array) data_get($document, 'summary.chiffres_cites', []),
                    array_filter([(string) data_get($document, 'summary.resultat_traitement', '')])
                ),
                $referenceSummaries
            ))),
            'objections_probables' => array_values(array_merge(...array_map(
                fn ($document) => (array) data_get($document, 'summary.objections_frequentes', []),
                $referenceSummaries
            ))),
        ];
    }

    protected function maskedPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = PhoneNumber::digits($phone);
        if (strlen($digits) < 5) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 2) . str_repeat('*', max(1, strlen($digits) - 4)) . substr($digits, -2);
    }

    protected function visibleProspect(AgentExecutionContext $context, int $prospectId): ?Prospect
    {
        $visible = $this->reader->visibleProspectIds($context, [$prospectId], false);

        if (!$visible['ids']) {
            return null;
        }

        return Prospect::query()->find($visible['ids'][0]);
    }

}
