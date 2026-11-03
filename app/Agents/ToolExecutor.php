<?php

namespace App\Agents;

use App\Agents\Tools\AgentToolInterface;
use App\Agents\Tools\InputValidator;
use App\Agents\Tools\ToolRegistry;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\ConfirmsLargeActions;
use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\AgentRun;
use App\Models\AgentRunStep;
use Illuminate\Database\QueryException;

/**
 * Runs one tool call of the model, safely.
 *
 * In this order: the tool must exist, its input must follow its schema, the
 * PermissionGate decides (live rights of the owner in the project), then the
 * tool runs as the owner (AgentExecutionContext), or is kept as an action a
 * human approves. Every call leaves one row in agent_run_steps.
 *
 * A tool call runs at most once per run: a write is never executed again when
 * a job is retried after a crash (same guarantee as the workflow nodes). A
 * read, harmless, is simply run again.
 */
class ToolExecutor
{
    public function __construct(
        protected ToolRegistry $tools,
        protected PermissionGate $gate,
    ) {
    }

    /**
     * @param  array{id: string, name: string, input: array}  $toolUse
     * @param  string[]  $allowedToolKeys  tools of the skills of the agent
     * @param  array<string, string>  $grants  tool key => allow | deny
     * @return array the tool_result block to send back to the model
     */
    public function execute(
        AgentRun $run,
        Agent $agent,
        AgentExecutionContext $context,
        array $toolUse,
        array $allowedToolKeys,
        array $grants,
    ): array {
        $started = microtime(true);
        $tool = $this->tools->get($toolUse['name']);

        $step = $this->openStep($run, $toolUse, $tool);

        if ($step === null) {
            return $this->block($toolUse['id'], 'Cet appel a déjà été traité avant une interruption : il n\'est pas rejoué. Vérifie son effet dans le CRM avant de continuer.', true);
        }

        if (!$tool) {
            return $this->finish($step, $toolUse, AgentRunStep::DENIED, 'deny', ToolResult::error("Outil inconnu : {$toolUse['name']}.", ToolResult::NOT_FOUND), $started);
        }

        $errors = InputValidator::validate($tool->inputSchema(), $toolUse['input']);

        if ($errors) {
            return $this->finish($step, $toolUse, AgentRunStep::FAILED, null, ToolResult::error('Paramètres invalides : ' . implode(' ', $errors), ToolResult::INVALID_INPUT), $started);
        }

        // Rights are read now, never kept from an earlier call. The owner who
        // lost the access to the project makes run() throw: the run stops.
        $decision = $context->run(function () use ($tool, $agent, $context, $allowedToolKeys, $grants) {
            $ability = $tool->ability() === null
                ? null
                : (bool) auth()->user()->can($tool->ability(), $context->project);

            return $this->gate->decide($tool, $agent->autonomy, $allowedToolKeys, $grants, $ability);
        });

        $step->permission_decision = $decision->outcome;

        if ($decision->isDenied()) {
            return $this->finish($step, $toolUse, AgentRunStep::DENIED, $decision->outcome, ToolResult::error($decision->reason, ToolResult::PERMISSION_DENIED), $started);
        }

        // An action that reaches many records is decided by a human, whatever the autonomy
        if (!$decision->needsApproval() && $tool instanceof ConfirmsLargeActions) {
            $threshold = (int) config('agents.bulk_confirm_threshold', 20);
            $count = $context->run(fn () => $tool->affectedCount($context, $toolUse['input']));

            if ($count > $threshold) {
                $decision = PermissionDecision::approve("Cette action touche $count enregistrements (plus de $threshold) : elle attend votre validation.");
                $step->permission_decision = $decision->outcome;
            }
        }

        if ($decision->needsApproval()) {
            $action = $this->keepForApproval($run, $agent, $context, $tool, $toolUse);

            return $this->finish(
                $step,
                $toolUse,
                AgentRunStep::PENDING_APPROVAL,
                $decision->outcome,
                ToolResult::ok(
                    ['status' => 'pending_approval', 'action_id' => $action->id, 'message' => "Cette action n'est PAS exécutée : elle attend la validation de l'utilisateur. Dis-le-lui clairement."],
                    "Action {$action->id} en attente de validation"
                ),
                $started
            );
        }

        try {
            $result = $context->run(fn () => $tool->handle($context, $toolUse['input']));
        } catch (AgentAccessException $e) {
            // Lost access to the project: not an error of the tool
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            // The message of an internal error is not shown to the model
            $result = ToolResult::error("L'outil {$toolUse['name']} a rencontré une erreur interne.", ToolResult::INTERNAL_ERROR, true);
        }

        return $this->finish($step, $toolUse, $result->isError ? AgentRunStep::FAILED : AgentRunStep::COMPLETED, $decision->outcome, $result, $started);
    }

    /**
     * Records the call. Null: a write with this id was already started.
     */
    protected function openStep(AgentRun $run, array $toolUse, ?AgentToolInterface $tool): ?AgentRunStep
    {
        $existing = $run->steps()->where('tool_use_id', $toolUse['id'])->first();

        if ($existing) {
            if ($tool && $tool->risk() === AgentToolInterface::READ) {
                $existing->update(['status' => AgentRunStep::RUNNING, 'error' => null]);

                return $existing;
            }

            return null;
        }

        try {
            return AgentRunStep::create([
                'run_id' => $run->id,
                'seq' => ((int) $run->steps()->max('seq')) + 1,
                'type' => AgentRunStep::TOOL_CALL,
                'tool_key' => $toolUse['name'],
                'tool_use_id' => $toolUse['id'],
                'status' => AgentRunStep::RUNNING,
                // Kept short: the audit log keeps traces, not the CRM data
                'input' => strlen((string) json_encode($toolUse['input'])) <= 2000 ? $toolUse['input'] : ['_truncated' => true],
            ]);
        } catch (QueryException) {
            // Another worker recorded the same call at the same time
            return null;
        }
    }

    protected function keepForApproval(AgentRun $run, Agent $agent, AgentExecutionContext $context, AgentToolInterface $tool, array $toolUse): AgentAction
    {
        $preview = $context->run(fn () => $tool->preview($context, $toolUse['input']));

        return AgentAction::create([
            'run_id' => $run->id,
            'agent_id' => $agent->id,
            'user_id' => $context->owner->id,
            'project_id' => $context->project->id,
            'tool_key' => $tool->key(),
            'tool_use_id' => $toolUse['id'],
            'risk' => $tool->risk(),
            'payload' => $toolUse['input'],
            'preview' => $preview,
            'status' => AgentAction::PENDING,
        ]);
    }

    protected function finish(AgentRunStep $step, array $toolUse, string $status, ?string $decision, ToolResult $result, float $started): array
    {
        $content = $result->toContent();
        $limit = (int) config('agents.max_tool_result_chars', 30000);

        if (mb_strlen($content) > $limit) {
            $result = ToolResult::error('Le résultat est trop volumineux pour être transmis. Restreins la demande (filtres, période, moins de champs).', ToolResult::LIMIT_EXCEEDED);
            $content = $result->toContent();
            $status = AgentRunStep::FAILED;
        }

        $step->update([
            'status' => $status,
            'permission_decision' => $decision ?? $step->permission_decision,
            'output_summary' => mb_substr($result->summary, 0, 500),
            'rows' => $result->rows,
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'error' => $result->isError ? mb_substr($result->summary, 0, 500) : null,
        ]);

        return $this->block($toolUse['id'], $content, $result->isError || $status === AgentRunStep::DENIED);
    }

    protected function block(string $toolUseId, string $content, bool $isError): array
    {
        $block = ['type' => 'tool_result', 'tool_use_id' => $toolUseId, 'content' => $content];

        if ($isError) {
            $block['is_error'] = true;
        }

        return $block;
    }
}
