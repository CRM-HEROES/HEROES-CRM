<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;

/**
 * What was deleted and can still be brought back: prospects, orders and appointments. A read: it gives the
 * identifiers that trash_restore needs.
 */
class TrashListTool implements AgentToolInterface
{
    public function __construct(protected TrashItems $items)
    {
    }

    public function key(): string
    {
        return 'trash_list';
    }

    public function description(): string
    {
        return "Liste ce qui a été supprimé et peut être restauré (corbeille du CRM), du plus récemment supprimé au plus ancien : prospects, commandes ou rendez-vous. "
            . "Donne les identifiants à utiliser avec trash_restore. Pour un prospect, search filtre sur le nom ou la société.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type' => ['type' => 'string', 'enum' => TrashItems::types(), 'description' => 'Ce qu\'on cherche dans la corbeille.'],
                'search' => ['type' => 'string', 'maxLength' => 100, 'description' => 'type=prospect : une partie du nom, du prénom ou de la société.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => '20 par défaut.'],
            ],
            'required' => ['type'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'trash';
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
        $type = $input['type'];
        $limit = max(1, min(50, (int) ($input['limit'] ?? 20)));
        $query = $this->items->query($context, $type);

        if ($type === 'prospect' && ($search = trim((string) ($input['search'] ?? ''))) !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr($search, 0, 100)) . '%';
            $query->where(fn ($q) => $q->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->orWhere('company_name', 'like', $like));
        }

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('deleted_at')->limit($limit)->get();

        return ToolResult::ok(
            [
                'type' => $type,
                'total' => $total,
                'items' => $rows->map(fn ($row) => $this->items->describe($type, $row))->all(),
                'restore_with' => 'trash_restore',
            ],
            'Corbeille : ' . TrashItems::TYPES[$type]['label'] . 's supprimés',
            $rows->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
