<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Models\WorkflowExecution;
use App\Workflows\WorkflowMonitor;

/**
 * The follow-up of the automations (workflows): what ran, what waits, what failed and why. It reads through the
 * monitor the "Suivi" tab of the interface uses, for the project of the run only, and needs the same right as that tab.
 *
 * Nothing personal is added: the names of prospects are the ones the monitor already shows to a user who may see them,
 * texts are cut, and the result of a step is reduced to a few technical fields.
 */
class WorkflowExecutionsTool implements AgentToolInterface
{
    public const VIEWS = ['overview', 'list', 'detail', 'prospect'];
    public const STATUSES = ['pending', 'running', 'waiting', 'completed', 'failed', 'cancelled', 'skipped'];

    /** What a step keeps of its result: technical fields only */
    protected const RESULT_KEYS = ['provider', 'sms_id', 'message_id', 'call_attempt_id', 'status', 'outcome', 'scheduled_at', 'reason'];

    public function __construct(protected WorkflowMonitor $monitor, protected ReadGateway $reader)
    {
    }

    public function key(): string
    {
        return 'workflow_executions';
    }

    public function description(): string
    {
        return "Suivi des automatisations (workflows) du projet. view=overview : chiffres des derniers jours (en cours, en attente, terminées, en échec, erreurs les plus fréquentes, opérateurs utilisés). "
            . "view=list : les exécutions les plus récentes, filtrables par workflow_id, status ou prospect. view=detail : une exécution avec ses étapes et l'erreur de chacune. "
            . "view=prospect : ce que les automatisations ont fait à un prospect. Sert à expliquer pourquoi un envoi ou une étape a échoué.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'view' => ['type' => 'string', 'enum' => self::VIEWS],
                'days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 90, 'description' => 'view=overview : nombre de jours (7 par défaut).'],
                'workflow_id' => ['type' => 'integer', 'description' => 'view=list : limiter à un workflow.'],
                'status' => ['type' => 'string', 'enum' => self::STATUSES, 'description' => 'view=list : limiter à un état (failed pour les échecs).'],
                'prospect_id' => ['type' => 'integer', 'description' => 'view=list ou view=prospect : un prospect.'],
                'execution_id' => ['type' => 'integer', 'description' => 'view=detail : une exécution.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'view=list ou view=prospect : nombre maximal (20 par défaut).'],
            ],
            'required' => ['view'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'workflow';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'projectWorkflowExecutionView';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        return match ($input['view']) {
            'overview' => $this->overview($context, (int) ($input['days'] ?? 7)),
            'list' => $this->list($context, $input),
            'detail' => $this->detail($context, (int) ($input['execution_id'] ?? 0)),
            'prospect' => $this->forProspect($context, (int) ($input['prospect_id'] ?? 0), (int) ($input['limit'] ?? 20)),
            default => ToolResult::error('Vue inconnue : ' . $input['view'] . '.', ToolResult::VALIDATION_FAILED),
        };
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function overview(AgentExecutionContext $context, int $days): ToolResult
    {
        $stats = $this->monitor->stats($context->project->id, max(1, min(90, $days)));

        // The messages of the errors are cut: they end up in the conversation
        $stats['errors'] = collect($stats['errors'] ?? [])
            ->map(fn ($error) => ['message' => $this->text($error['message'] ?? null), 'count' => $error['count'] ?? 0])
            ->all();

        return ToolResult::ok($stats, "Suivi des automatisations sur {$stats['days']} jour(s)", count($stats['workflows'] ?? []));
    }

    protected function list(AgentExecutionContext $context, array $input): ToolResult
    {
        $filters = array_filter([
            'workflow' => $input['workflow_id'] ?? null,
            'status' => $input['status'] ?? null,
        ]);

        if (!empty($input['prospect_id'])) {
            $visible = $this->reader->visibleProspectIds($context, [$input['prospect_id']], false);
            if (!$visible['ids']) {
                return $this->prospectNotFound($input['prospect_id']);
            }

            $filters['subject_type'] = 'prospect';
            $filters['subject_id'] = $visible['ids'][0];
        }

        $limit = max(1, min(50, (int) ($input['limit'] ?? 20)));
        $page = $this->monitor->executions($context->project->id, $filters, $limit);

        return ToolResult::ok(
            ['executions' => array_map(fn ($execution) => $this->summary($execution), $page['data']), 'has_more' => $page['has_more']],
            'Exécutions des automatisations',
            count($page['data'])
        );
    }

    protected function detail(AgentExecutionContext $context, int $id): ToolResult
    {
        $execution = $id > 0
            ? WorkflowExecution::query()->where('project_id', $context->project->id)->find($id)
            : null;

        if (!$execution) {
            return ToolResult::error("Exécution $id introuvable dans ce projet.", ToolResult::NOT_FOUND);
        }

        return ToolResult::ok($this->full($this->monitor->detail($execution)), "Exécution $id", 1);
    }

    protected function forProspect(AgentExecutionContext $context, int $prospectId, int $limit): ToolResult
    {
        $visible = $this->reader->visibleProspectIds($context, [$prospectId], false);
        if (!$visible['ids']) {
            return $this->prospectNotFound($prospectId);
        }

        $executions = collect($this->monitor->prospectHistory($context->project->id, $visible['ids'][0]))
            ->take(max(1, min(50, $limit)))
            ->map(fn ($execution) => $this->full($execution))
            ->values();

        return ToolResult::ok(
            ['prospect_id' => $visible['ids'][0], 'executions' => $executions->all()],
            "Automatisations du prospect {$visible['ids'][0]}",
            $executions->count()
        );
    }

    protected function prospectNotFound(int $id): ToolResult
    {
        return ToolResult::error("Prospect $id introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
    }

    /**
     * An execution without its steps.
     */
    protected function summary(array $execution): array
    {
        $execution['error'] = $this->text($execution['error'] ?? null);

        return $execution;
    }

    /**
     * An execution with its steps, reduced to what explains what happened.
     */
    protected function full(array $execution): array
    {
        $execution = $this->summary($execution);
        $execution['steps'] = array_map(function (array $step) {
            return [
                'name' => $step['name'] ?? null,
                'type' => $step['type'] ?? null,
                'action' => $step['action'] ?? null,
                'trigger' => $step['trigger'] ?? null,
                'status' => $step['status'] ?? null,
                'branch' => $step['branch'] ?? null,
                'attempts' => $step['attempts'] ?? null,
                'error' => $this->text($step['error'] ?? null),
                'delivery' => $step['delivery'] ?? null,
                'result' => array_intersect_key(
                    array_filter((array) ($step['result'] ?? []), 'is_scalar'),
                    array_flip(self::RESULT_KEYS)
                ),
                'started_at' => $step['started_at'] ?? null,
                'finished_at' => $step['finished_at'] ?? null,
            ];
        }, $execution['steps'] ?? []);

        return $execution;
    }

    protected function text(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr(strip_tags($value), 0, 300) : null;
    }
}
