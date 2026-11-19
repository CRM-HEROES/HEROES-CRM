<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Services\IncomingCommunications;

/**
 * What came in and is still to process: SMS received, e-mails received and voicemails. It gives the numbers of the
 * badge of the header and, by prospect, the identifiers of the communications waiting. The text of a message is not
 * part of it: crm_search (sms, email) reads it, on demand and truncated.
 *
 * Everything is limited to the prospects the owner of the agent may see, as the badge is.
 */
class IncomingCommunicationsTool implements AgentToolInterface
{
    public function __construct(protected IncomingCommunications $communications)
    {
    }

    public function key(): string
    {
        return 'incoming_communications';
    }

    public function description(): string
    {
        return "Communications reçues à traiter (SMS reçus, e-mails reçus, messages vocaux). view=count : le nombre de prospects concernés, par type (comme le badge « Communications reçues »). "
            . "view=pending : les prospects qui ont des communications à traiter, avec les identifiants par type (sms, email, voicemail), pour un prospect (prospect_id) ou les plus récents. "
            . "view=history : qui a traité les SMS ou e-mails reçus d'un prospect, et quand. Le texte des messages se lit avec crm_search (entités sms et email).";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'view' => ['type' => 'string', 'enum' => ['count', 'pending', 'history']],
                'prospect_id' => ['type' => 'integer', 'description' => 'view=pending (facultatif) ou view=history (obligatoire).'],
                'type' => ['type' => 'string', 'enum' => ['sms', 'email'], 'description' => 'view=history : sms par défaut.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'view=pending : 20 par défaut.'],
            ],
            'required' => ['view'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'incoming';
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
        $userId = $context->owner->id;

        try {
            return match ($input['view']) {
                'count' => ToolResult::ok($this->communications->counts($context->project, $userId), 'Communications reçues à traiter', 1),
                'pending' => $this->pending($context, $input),
                'history' => $this->history($context, $input),
                default => ToolResult::error('Vue inconnue : ' . $input['view'] . '.', ToolResult::VALIDATION_FAILED),
            };
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ToolResult::error('Prospect introuvable dans ce projet, ou non accessible pour cet utilisateur.', ToolResult::NOT_FOUND);
        }
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function pending(AgentExecutionContext $context, array $input): ToolResult
    {
        $data = $this->communications->pending($context->project, $context->owner->id, $input['prospect_id'] ?? null, (int) ($input['limit'] ?? 20));

        if (!empty($input['prospect_id']) && !$data['prospects']) {
            return ToolResult::ok(['total' => 0, 'prospects' => [], 'note' => 'Rien à traiter pour ce prospect (ou prospect non visible).'], 'Aucune communication à traiter', 0);
        }

        return ToolResult::ok($data, 'Communications à traiter', count($data['prospects']));
    }

    protected function history(AgentExecutionContext $context, array $input): ToolResult
    {
        if (empty($input['prospect_id'])) {
            return ToolResult::error('prospect_id est obligatoire pour view=history.', ToolResult::VALIDATION_FAILED);
        }

        $rows = $this->communications->readHistory($context->project, (int) $input['prospect_id'], $input['type'] ?? 'sms')
            ->take(50)
            ->map(fn ($row) => [
                'id' => $row->id,
                'treated_by' => $row->user_name,
                'treated_at' => $row->read_at,
            ])->values()->all();

        return ToolResult::ok(['prospect_id' => (int) $input['prospect_id'], 'history' => $rows], 'Historique de traitement', count($rows));
    }
}
