<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\WorkflowExecution;

/**
 * Stops an execution of an automation that is not over: it is pending or waiting (a delay, an event). The steps already
 * done stay done and nothing is undone; what was still to come does not happen. The CRM refuses an execution that a
 * worker is running right now or that is over.
 *
 * Stopping is the safe direction (a message that would have gone out does not), but it cannot be resumed.
 */
class WorkflowCancelExecutionTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'workflow_cancel_execution';
    }

    public function description(): string
    {
        return "Annule une exécution d'automatisation qui n'est pas terminée (état pending ou waiting : en attente d'un délai ou d'un événement). Les étapes déjà faites restent faites ; la suite n'aura pas lieu et l'exécution ne se reprend pas. "
            . "Une exécution en cours de traitement, terminée ou échouée ne s'annule pas. Retrouve l'exécution avec workflow_executions (list ou detail) et vérifie son état avant d'annuler. "
            . "Pour arrêter toutes les exécutions futures d'un workflow, c'est workflow_set_status (pause).";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'execution_id' => ['type' => 'integer', 'description' => 'Exécution à annuler (workflow_executions).'],
            ],
            'required' => ['execution_id'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'workflow';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return 'projectWorkflowExecute';
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        return $this->problem($this->find($context, (int) $input['execution_id']), $input['execution_id']);
    }

    protected function problem(?WorkflowExecution $execution, mixed $id): ?ToolResult
    {
        if (!$execution) {
            return ToolResult::error("Exécution $id introuvable dans ce projet.", ToolResult::NOT_FOUND);
        }
        if (!in_array($execution->status, [WorkflowExecution::PENDING, WorkflowExecution::WAITING], true)) {
            return ToolResult::error("Cette exécution est « {$execution->status} » : seules celles en attente (pending ou waiting) s'annulent.", ToolResult::INVALID_INPUT);
        }

        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $execution = $this->find($context, (int) $input['execution_id']);

        if ($error = $this->problem($execution, $input['execution_id'])) {
            return $error;
        }

        $response = $this->api->call($context, 'POST', "workflow-execution/{$execution->id}/cancel");

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(
            ['execution_id' => $execution->id, 'status' => $response->json['status'] ?? WorkflowExecution::CANCELLED],
            "Exécution {$execution->id} annulée",
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $execution = $this->find($context, (int) ($input['execution_id'] ?? 0));

        return [
            'action' => "Annuler l'exécution de l'automatisation (la suite n'aura pas lieu)",
            'execution_id' => $input['execution_id'] ?? null,
            'status' => $execution?->status,
        ];
    }

    protected function find(AgentExecutionContext $context, int $id): ?WorkflowExecution
    {
        return $id > 0 ? WorkflowExecution::query()->where('project_id', $context->project->id)->find($id) : null;
    }
}
