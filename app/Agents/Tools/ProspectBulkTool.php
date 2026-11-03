<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\AgentReadException;
use App\Agents\Entities\EntityRegistry;
use App\Agents\Entities\ReadGateway;
use App\Agents\Writes\ConfirmsLargeActions;
use App\Agents\Writes\InternalApi;
use App\Models\Prospect;
use Closure;

/**
 * An action on a set of prospects, defined by data: the tools "set label",
 * "assign user", "delete"... are instances of this class, not new code.
 *
 * The set is given by ids (up to 200) or by filters (the ones of crm_search on
 * the prospect entity); the server resolves it, as the owner, so only the
 * prospects the owner can see are reached. The records are sent to the route of
 * the CRM by chunks (bulk_chunk), never one request per record, and the answer
 * is a count: {processed, updated, failed}.
 *
 * Definition keys:
 *   key, description, risk, ability, label (what the action does, for the preview)
 *   schema      extra input properties (JSON Schema), required: names
 *   editable    true: processed prospects are skipped (the CRM locks them)
 *   count_key   key of the answer that tells how many records the route really handled
 *   check       fn (context, input): ?ToolResult  — extra validation, e.g. the label exists
 *   request     fn (input, int[] ids, context): array [method, path, body]
 *   describe    fn (context, input): string  — what will be done, for the human who approves
 */
class ProspectBulkTool implements AgentToolInterface, ConfirmsLargeActions
{
    public function __construct(
        protected array $definition,
        protected InternalApi $api,
        protected ReadGateway $reader,
        protected EntityRegistry $entities,
    ) {
    }

    public function key(): string
    {
        return $this->definition['key'];
    }

    public function description(): string
    {
        return $this->definition['description']
            . " Cible : soit prospect_ids (200 au plus), soit filters (les mêmes filtres que crm_search sur l'entité prospect) ; jamais les deux. "
            . "Seuls les prospects que tu peux voir sont atteints, les fiches déjà traitées sont "
            . ($this->definition['editable'] ? 'ignorées' : 'incluses') . '. '
            . "Au-delà de " . config('agents.bulk_confirm_threshold', 20) . " prospects, l'utilisateur doit valider avant l'exécution : vérifie d'abord le nombre avec crm_search (total).";
    }

    public function inputSchema(): array
    {
        $filters = [];

        foreach ($this->entities->get('prospect')['filters'] as $name => $filter) {
            $filters[$name] = ['type' => $filter['type'] === 'date' ? 'string' : $filter['type'], 'description' => $filter['label']];
        }

        return [
            'type' => 'object',
            'properties' => [
                'prospect_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 200, 'description' => 'Identifiants des prospects (200 au plus).'],
                'filters' => ['type' => 'object', 'properties' => $filters, 'additionalProperties' => false, 'description' => 'Filtres de sélection (comme crm_search, entité prospect).'],
            ] + ($this->definition['schema'] ?? []),
            'required' => $this->definition['required'] ?? [],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'prospect';
    }

    public function risk(): string
    {
        return $this->definition['risk'];
    }

    public function ability(): ?string
    {
        return $this->definition['ability'];
    }

    public function affectedCount(AgentExecutionContext $context, array $input): int
    {
        try {
            return $this->select($context, $input)['reach'];
        } catch (AgentReadException) {
            // The call itself will answer with the reason
            return 0;
        }
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        try {
            $selection = $this->select($context, $input);
        } catch (AgentReadException $e) {
            return ToolResult::error($e->getMessage(), $e->errorCode);
        }

        if ($error = $this->check($context, $input)) {
            return $error;
        }

        if ($selection['truncated']) {
            return ToolResult::error('La sélection dépasse ' . $this->max() . ' prospects : affine les filtres.', ToolResult::LIMIT_EXCEEDED);
        }

        $ids = $selection['ids'];

        if (!$ids) {
            return ToolResult::ok($this->counts($selection, 0, 0) + ['message' => 'Aucun prospect à traiter.'], 'Aucun prospect à traiter', 0);
        }

        $chunk = max(1, (int) config('agents.bulk_chunk', 500));
        $updated = 0;
        $failed = 0;
        $notApplied = 0;
        $lastError = null;
        $done = 0;

        foreach (array_chunk($ids, $chunk) as $part) {
            [$method, $path, $body] = ($this->definition['request'])($input, $part, $context);
            $response = $this->api->call($context, $method, $path, $body);

            if ($response->ok()) {
                // Some routes say how many they really handled (the CRM may skip some on its own rules)
                $applied = isset($this->definition['count_key']) && is_int($response->json[$this->definition['count_key']] ?? null)
                    ? min(count($part), $response->json[$this->definition['count_key']])
                    : count($part);
                $updated += $applied;
                $notApplied += count($part) - $applied;
            } else {
                $failed += count($part);
                $lastError = $response->toError();
            }

            $done += count($part);
            $context->reportProgress($done, count($ids));
        }

        if ($updated === 0 && $lastError) {
            return $lastError;
        }

        $counts = $this->counts($selection, $updated, $failed) + ($notApplied ? ['not_applied_by_crm' => $notApplied, 'note' => "Le CRM a ignoré ces prospects selon ses propres règles (par exemple un SMS en masse n'est envoyé qu'aux prospects assignés à l'utilisateur)."] : []);

        return ToolResult::ok(
            $counts + ($lastError ? ['last_error' => $lastError->data['error']] : []),
            "{$this->key()} : {$counts['updated']} mis à jour, {$counts['failed']} en échec sur {$counts['processed']}",
            $updated
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        try {
            $selection = $this->select($context, $input);
        } catch (AgentReadException $e) {
            return ['error' => $e->getMessage()];
        }

        $sample = Prospect::query()->whereIn('prospects.id', array_slice($selection['ids'], 0, 5))
            ->get(['id', 'first_name', 'last_name', 'company_name'])
            ->map(fn ($p) => trim("{$p->first_name} {$p->last_name}") . ($p->company_name ? " ({$p->company_name})" : '') . " #{$p->id}")
            ->all();

        return [
            'action' => ($this->definition['describe'])($context, $input),
            'prospects' => count($selection['ids']),
            'skipped_processed' => $selection['skipped'],
            'sample' => $sample,
        ];
    }

    /**
     * @return array{ids: int[], reach: int, skipped: int, truncated: bool, matched: int}
     */
    protected function select(AgentExecutionContext $context, array $input): array
    {
        $hasIds = !empty($input['prospect_ids']);
        $hasFilters = !empty($input['filters']);
        $editable = (bool) ($this->definition['editable'] ?? true);

        if ($hasIds === $hasFilters) {
            throw new AgentReadException('Donne soit prospect_ids, soit filters (un seul des deux, non vide). Une sélection vide n\'est jamais interprétée comme "tous les prospects".');
        }

        if ($hasIds) {
            $found = $this->reader->visibleProspectIds($context, $input['prospect_ids'], $editable);

            return [
                'ids' => $found['ids'], 'reach' => count($found['ids']), 'truncated' => false,
                'skipped' => $found['processed'] * ($editable ? 1 : 0), 'matched' => count($found['ids']) + $found['unknown'],
            ];
        }

        $found = $this->reader->selectProspects($context, $input['filters'], $this->max(), $editable);

        return [
            'ids' => $found['ids'],
            // The real number, even when the list is cut: the human must see it
            'reach' => $found['total'] - ($editable ? $found['processed'] : 0),
            'truncated' => $found['truncated'],
            'skipped' => $editable ? $found['processed'] : 0,
            'matched' => $found['total'],
        ];
    }

    protected function check(AgentExecutionContext $context, array $input): ?ToolResult
    {
        return isset($this->definition['check']) ? ($this->definition['check'])($context, $input) : null;
    }

    protected function counts(array $selection, int $updated, int $failed): array
    {
        return [
            'processed' => $updated + $failed,
            'updated' => $updated,
            'failed' => $failed,
            'skipped_processed' => $selection['skipped'],
        ];
    }

    protected function max(): int
    {
        return (int) config('agents.bulk_max', 20000);
    }
}
