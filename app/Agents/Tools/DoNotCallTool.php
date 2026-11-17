<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Calls\ProspectCallConsent;
use App\Models\Prospect;

class DoNotCallTool implements AgentToolInterface
{
    public function __construct(
        protected ReadGateway $reader,
        protected ProspectCallConsent $consent,
    ) {
    }

    public function key(): string
    {
        return 'do_not_call';
    }

    public function description(): string
    {
        return 'Enregistre la demande d’un prospect de ne plus être appelé. Requiert son consentement explicite et ne contacte aucun fournisseur.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['prospect_id' => ['type' => 'integer', 'minimum' => 1]],
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
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return 'prospectUpdate';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $visible = $this->reader->visibleProspectIds($context, [(int) $input['prospect_id']], false);
        $prospect = $visible['ids'] ? Prospect::query()->find($visible['ids'][0]) : null;
        if (!$prospect) {
            return ToolResult::error('Prospect introuvable ou non accessible.', ToolResult::NOT_FOUND);
        }

        $this->consent->update($prospect, false, true, auth()->id());

        return ToolResult::ok(['do_not_call' => true], 'Demande de ne plus appeler enregistrée', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return ['action' => 'Enregistrer la demande de ne plus appeler'];
    }
}
