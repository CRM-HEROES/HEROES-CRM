<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\ConfirmsLargeActions;
use App\Agents\Writes\InternalApi;

/**
 * Brings back prospects, orders or appointments from the trash.
 *
 * The route of the CRM (PUT trash/bulk) does not check any right itself, so the tool does: restoring needs the right
 * that deleting needs. Only what is really in the trash of this project is sent to the route.
 */
class TrashRestoreTool implements AgentToolInterface, ConfirmsLargeActions, ChecksBeforeApproval
{
    public const MAX = 100;

    public function __construct(protected TrashItems $items, protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'trash_restore';
    }

    public function description(): string
    {
        return "Restaure depuis la corbeille des prospects, des commandes ou des rendez-vous supprimés (identifiants donnés par trash_list). "
            . "Il faut le droit de suppression correspondant. Une commande ou un rendez-vous ne revient que si son prospect n'est pas supprimé : restaure d'abord le prospect. "
            . 'Au-delà de ' . config('agents.bulk_confirm_threshold', 20) . ' éléments, l\'utilisateur doit valider avant l\'exécution.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => ['type' => 'string', 'enum' => TrashItems::types()],
                'ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'minItems' => 1, 'maxItems' => self::MAX, 'description' => 'Identifiants à restaurer (trash_list).'],
            ],
            'required' => ['type', 'ids'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'trash';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    /** The right depends on the type: it is checked in handle() */
    public function ability(): ?string
    {
        return null;
    }

    public function affectedCount(AgentExecutionContext $context, array $input): int
    {
        return count($this->ids($input));
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $resolved = $this->resolve($context, $input);

        return $resolved instanceof ToolResult ? $resolved : null;
    }

    /**
     * What is really in the trash among the ids asked: [found, missing], or the refusal.
     *
     * @return ToolResult|array{0: int[], 1: int[]}
     */
    protected function resolve(AgentExecutionContext $context, array $input): ToolResult|array
    {
        $type = $input['type'];
        $ids = $this->ids($input);

        if (!$ids) {
            return ToolResult::error('Aucun identifiant à restaurer.', ToolResult::VALIDATION_FAILED);
        }
        if (count($ids) > self::MAX) {
            return ToolResult::error('Au plus ' . self::MAX . ' éléments à la fois.', ToolResult::LIMIT_EXCEEDED);
        }
        if (!$context->owner->can(TrashItems::TYPES[$type]['right'], $context->project)) {
            return ToolResult::error("L'utilisateur propriétaire de l'agent n'a pas le droit de supprimer (donc de restaurer) ce type d'élément dans ce projet.", ToolResult::PERMISSION_DENIED);
        }

        $found = $this->items->query($context, $type)->whereIn($this->column($type), $ids)->pluck($this->column($type))->map(fn ($id) => (int) $id)->all();

        if (!$found) {
            return ToolResult::error('Aucun de ces éléments n\'est dans la corbeille de ce projet (ou son prospect est lui-même supprimé).', ToolResult::NOT_FOUND);
        }

        return [$found, array_values(array_diff($ids, $found))];
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $type = $input['type'];
        $resolved = $this->resolve($context, $input);

        if ($resolved instanceof ToolResult) {
            return $resolved;
        }

        [$found, $missing] = $resolved;

        $response = $this->api->call($context, 'PUT', 'trash/bulk', ['action' => 'restore', TrashItems::TYPES[$type]['route'] => $found]);

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(
            ['type' => $type, 'restored' => count($found), 'restored_ids' => $found, 'not_in_trash' => $missing],
            count($found) . ' élément(s) restauré(s) : ' . TrashItems::TYPES[$type]['label'],
            count($found)
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $type = $input['type'] ?? null;
        if (!in_array($type, TrashItems::types(), true)) {
            return [];
        }

        $rows = $this->items->query($context, $type)->whereIn($this->column($type), $this->ids($input))->limit(10)->get();

        return [
            'summary' => 'Restaurer ' . count($this->ids($input)) . ' ' . TrashItems::TYPES[$type]['label'] . '(s) depuis la corbeille',
            'items' => $rows->map(fn ($row) => $this->items->describe($type, $row))->all(),
        ];
    }

    protected function ids(array $input): array
    {
        return array_values(array_unique(array_filter(array_map('intval', (array) ($input['ids'] ?? [])), fn ($id) => $id > 0)));
    }

    protected function column(string $type): string
    {
        return ['prospect' => 'prospects.id', 'order' => 'orders.id', 'event' => 'events.id'][$type];
    }
}
