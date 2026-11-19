<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\ConfirmsLargeActions;
use App\Agents\Writes\InternalApi;
use App\Services\IncomingCommunications;

/**
 * Marks received communications of a prospect as treated ("Demande traité"). It is shared by the whole project: once
 * treated, a communication leaves the list of every other user, and the agent's owner is recorded as the one who treated it.
 *
 * Only communications the agent was told to treat should be marked: reading is not treating.
 */
class IncomingMarkTreatedTool implements AgentToolInterface, ConfirmsLargeActions
{
    public const MAX = 100;

    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'incoming_mark_treated';
    }

    public function description(): string
    {
        return "Marque comme traitées des communications reçues d'un prospect (SMS, e-mails ou messages vocaux), comme « Demande traité ». Les identifiants viennent de incoming_communications (view=pending). "
            . "C'est partagé : une communication traitée disparaît de la liste de tous les utilisateurs et c'est le propriétaire de l'agent qui est enregistré comme l'ayant traitée. "
            . "Ne marque que ce que l'utilisateur t'a demandé de traiter ; lire un message n'est pas le traiter. "
            . 'Au-delà de ' . config('agents.bulk_confirm_threshold', 20) . ' communications, l\'utilisateur doit valider.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'prospect_id' => ['type' => 'integer'],
                'type' => ['type' => 'string', 'enum' => array_keys(IncomingCommunications::TYPES)],
                'ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'minItems' => 1, 'maxItems' => self::MAX, 'description' => 'Identifiants des communications de ce type et de ce prospect.'],
            ],
            'required' => ['prospect_id', 'type', 'ids'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'incoming';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function affectedCount(AgentExecutionContext $context, array $input): int
    {
        return count($this->ids($input));
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $ids = $this->ids($input);

        if (!$ids) {
            return ToolResult::error('Aucun identifiant à traiter.', ToolResult::VALIDATION_FAILED);
        }
        if (count($ids) > self::MAX) {
            return ToolResult::error('Au plus ' . self::MAX . ' communications à la fois.', ToolResult::LIMIT_EXCEEDED);
        }

        $response = $this->api->call($context, 'POST', 'incoming-communications/read', [
            'prospect_id' => (int) $input['prospect_id'],
            'type' => $input['type'],
            'ids' => $ids,
        ]);

        if (!$response->ok()) {
            return $response->toError();
        }

        $marked = (int) ($response->json['marked'] ?? 0);

        return ToolResult::ok(
            [
                'prospect_id' => (int) $input['prospect_id'],
                'type' => $input['type'],
                'marked' => $marked,
                'already_treated_or_not_found' => count($ids) - $marked,
                'remaining' => array_intersect_key($response->json ?? [], array_flip(['count', 'sms', 'email', 'voicemail'])),
            ],
            "$marked communication(s) marquée(s) comme traitée(s)",
            $marked
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [
            'action' => 'Marquer comme traité (pour tous les utilisateurs)',
            'type' => $input['type'] ?? null,
            'prospect_id' => $input['prospect_id'] ?? null,
            'count' => count($this->ids($input)),
        ];
    }

    protected function ids(array $input): array
    {
        return array_values(array_unique(array_filter(array_map('intval', (array) ($input['ids'] ?? [])), fn ($id) => $id > 0)));
    }
}
