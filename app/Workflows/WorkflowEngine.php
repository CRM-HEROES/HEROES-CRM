<?php

namespace App\Workflows;

use App\Jobs\Workflow\RunWorkflowExecution;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Models\WorkflowVersion;
use App\Workflows\Nodes\ActionNode;
use App\Workflows\Nodes\ConditionNode;
use App\Workflows\Nodes\EndNode;
use App\Workflows\Nodes\NodeContext;
use App\Workflows\Nodes\ThrottledNodeInterface;
use App\Workflows\Nodes\TriggerNode;
use App\Workflows\Nodes\WaitEventNode;
use App\Workflows\Nodes\WaitNode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Runs workflow executions, one node at a time.
 *
 * Guarantees
 *  - One runner per execution: the execution is claimed with a single atomic
 *    UPDATE (pending|waiting -> running). Whoever loses the race does nothing.
 *  - A node is executed at most once per execution (unique key on
 *    execution_id + node_id). If a worker dies while a node runs, the node is
 *    NOT run again: the execution fails with an explicit message, which is
 *    preferred to sending a SMS or an email twice. A node that finished but
 *    whose successor was not recorded is not re-executed either: its stored
 *    result is replayed.
 *  - No transaction is ever open while a node runs (and so none during an
 *    external call): every state change is one short statement.
 *  - Nothing blocks on a wait: the execution is stored as "waiting" with the
 *    date it must resume, and `workflows:resume` wakes it up.
 *  - The workflow state is honored at every run: draft / paused workflows hold
 *    their executions (they are picked up again when the workflow is active),
 *    archived or deleted workflows cancel them.
 */
class WorkflowEngine
{
    protected const EXECUTORS = [
        WorkflowGraph::TRIGGER => TriggerNode::class,
        WorkflowGraph::CONDITION => ConditionNode::class,
        WorkflowGraph::ACTION => ActionNode::class,
        WorkflowGraph::WAIT => WaitNode::class,
        WorkflowGraph::WAIT_EVENT => WaitEventNode::class,
        WorkflowGraph::END => EndNode::class,
    ];

    /**
     * Graphs already read by this runner, by version id
     */
    protected array $graphs = [];

    public function run(int $executionId): void
    {
        $execution = WorkflowExecution::find($executionId);

        if (!$execution || in_array($execution->status, WorkflowExecution::FINISHED, true)) {
            return;
        }

        $workflow = Workflow::withTrashed()
            ->select('id', 'status', 'deleted_at')
            ->find($execution->workflow_id);

        if (!$workflow || $workflow->trashed() || $workflow->status === Workflow::ARCHIVED) {
            $this->finish(
                $execution,
                WorkflowExecution::CANCELLED,
                'The workflow was archived or deleted.',
                [WorkflowExecution::PENDING, WorkflowExecution::WAITING]
            );
            return;
        }

        // Draft or paused: the execution is held as it is
        if ($workflow->status !== Workflow::ACTIVE) {
            return;
        }

        if (!$this->claim($execution)) {
            return;
        }

        try {
            $this->advance($execution);
        } catch (\Throwable $e) {
            report($e);
            $this->finish($execution, WorkflowExecution::FAILED, $e->getMessage());
        }
    }

    /**
     * Fail an execution from outside the engine (job given up)
     */
    public function abort(int $executionId, string $reason): void
    {
        $execution = WorkflowExecution::find($executionId);

        if ($execution) {
            $this->finish($execution, WorkflowExecution::FAILED, $reason, [
                WorkflowExecution::PENDING,
                WorkflowExecution::WAITING,
                WorkflowExecution::RUNNING,
            ]);
        }
    }

    /**
     * pending|waiting -> running, atomically, and only when due.
     */
    protected function claim(WorkflowExecution $execution): bool
    {
        $claimed = WorkflowExecution::query()
            ->whereKey($execution->id)
            ->whereIn('status', [WorkflowExecution::PENDING, WorkflowExecution::WAITING])
            ->where(function ($query) {
                $query->whereNull('resume_at')->orWhere('resume_at', '<=', now());
            })
            ->update([
                'status' => WorkflowExecution::RUNNING,
                'locked_at' => now(),
                'updated_at' => now(),
            ]);

        if ($claimed) {
            $execution->refresh();
        }

        return (bool) $claimed;
    }

    protected function advance(WorkflowExecution $execution): void
    {
        $graph = $this->graph($execution->workflow_version_id);

        // A new execution starts on the trigger node
        $nodeId = $execution->current_node_id ?? ($graph->triggerNode()['id'] ?? null);

        $steps = 0;

        while ($nodeId !== null) {
            // Bounded work per job: hand over to a new one
            if (++$steps > config('workflows.max_steps_per_run')) {
                $execution->update([
                    'status' => WorkflowExecution::PENDING,
                    'current_node_id' => $nodeId,
                    'locked_at' => null,
                    'resume_at' => null,
                ]);
                RunWorkflowExecution::dispatch($execution->id);
                return;
            }

            $node = $graph->node($nodeId);

            if (!$node) {
                break;
            }

            $result = $this->executeNode($execution, $node);

            // An external service is saturated: the node was not run. Park the
            // execution ON this node, it will be tried again later.
            if ($result->status === NodeResult::DEFERRED) {
                $execution->update([
                    'status' => WorkflowExecution::WAITING,
                    'current_node_id' => $node['id'],
                    'resume_at' => $result->waitUntil,
                    'locked_at' => null,
                ]);
                return;
            }

            // "Wait for an event": park the execution ON this node. It comes back
            // when the event happens (WorkflowEventWaker) or at the timeout, and
            // the node then picks the "yes" or the "no" way (resumeEventWait).
            if ($result->status === NodeResult::EVENT_WAIT) {
                $execution->update([
                    'status' => WorkflowExecution::WAITING,
                    'current_node_id' => $node['id'],
                    'resume_at' => $result->waitUntil,
                    'waiting_event' => $result->awaitedEvent,
                    'locked_at' => null,
                ]);
                return;
            }

            // A failure the node accepts ("on_error": "continue"): recorded in the
            // log, the workflow goes on with the next node
            if ($result->status === NodeResult::FAILED && !$result->continue) {
                $this->finish($execution, WorkflowExecution::FAILED, $result->message);
                return;
            }

            if ($result->status === NodeResult::CANCELLED) {
                $this->finish($execution, WorkflowExecution::CANCELLED, $result->message);
                return;
            }

            $nodeId = $result->terminal ? null : $graph->next($node['id'], $result->branch);

            if ($nodeId !== null && $result->waitUntil) {
                // Park the execution on the node that follows the wait
                $execution->update([
                    'status' => WorkflowExecution::WAITING,
                    'current_node_id' => $nodeId,
                    'resume_at' => $result->waitUntil,
                    'locked_at' => null,
                ]);
                return;
            }

            if ($nodeId !== null) {
                $execution->update(['current_node_id' => $nodeId]);
            }
        }

        $this->finish($execution, WorkflowExecution::COMPLETED);
    }

    /**
     * Execute a node, once. Records the step in the execution log.
     */
    protected function executeNode(WorkflowExecution $execution, array $node): NodeResult
    {
        $previous = WorkflowNodeExecution::query()
            ->where('execution_id', $execution->id)
            ->where('node_id', $node['id'])
            ->first();

        if ($previous && $previous->status === WorkflowNodeExecution::WAITING) {
            return $this->resumeEventWait($execution, $previous);
        }

        if ($previous) {
            return $this->replay($previous);
        }

        $executor = app(self::EXECUTORS[$node['type']]);
        $context = new NodeContext($execution, $node);

        // Rate limit of the operator: decided before anything starts
        if ($executor instanceof ThrottledNodeInterface && ($until = $executor->delay($context))) {
            return NodeResult::deferred($until);
        }

        try {
            $row = WorkflowNodeExecution::create([
                'execution_id' => $execution->id,
                'node_id' => $node['id'],
                'node_type' => $node['type'],
                'status' => WorkflowNodeExecution::RUNNING,
                'started_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (WorkflowExecutionManager::isDuplicate($e)) {
                return NodeResult::failed("The step \"{$node['id']}\" is already being executed.");
            }
            throw $e;
        }

        try {
            $result = $executor->execute($context);
        } catch (\Throwable $e) {
            report($e);
            $result = NodeResult::failed($e->getMessage());
        }

        $row->update([
            'status' => [
                NodeResult::COMPLETED => WorkflowNodeExecution::COMPLETED,
                NodeResult::FAILED => WorkflowNodeExecution::FAILED,
                NodeResult::CANCELLED => WorkflowNodeExecution::SKIPPED,
                NodeResult::EVENT_WAIT => WorkflowNodeExecution::WAITING,
            ][$result->status],
            'branch' => $result->branch,
            'result' => ($result->data + ($result->continue ? ['continued' => true] : [])) ?: null,
            'error' => $result->message ? mb_substr($result->message, 0, 1000) : null,
            // A node that waits for an event is not finished yet
            'finished_at' => $result->status === NodeResult::EVENT_WAIT ? null : now(),
        ]);

        if ($result->status === NodeResult::FAILED) {
            Log::warning('Workflow step failed', [
                'execution_id' => $execution->id,
                'workflow_id' => $execution->workflow_id,
                'node_id' => $node['id'],
                'error' => $result->message,
            ]);
        }

        return $result;
    }

    /**
     * An execution comes back to its "wait for an event" node. If the event
     * happened, the waker cleared waiting_event (and woke the execution up);
     * if the timeout came first, waiting_event is still set.
     */
    protected function resumeEventWait(WorkflowExecution $execution, WorkflowNodeExecution $row): NodeResult
    {
        $happened = $execution->waiting_event === null;
        $branch = $happened ? WorkflowGraph::YES : WorkflowGraph::NO;
        $result = array_merge($row->result ?? [], ['outcome' => $happened ? 'event' : 'timeout']);

        $row->update([
            'status' => WorkflowNodeExecution::COMPLETED,
            'branch' => $branch,
            'result' => $result,
            'finished_at' => now(),
        ]);

        $execution->update(['waiting_event' => null]);

        return NodeResult::completed($result, $branch);
    }

    /**
     * Outcome of a node that already has a log row (job replayed):
     * a finished node is not executed again, an interrupted one is a failure.
     */
    protected function replay(WorkflowNodeExecution $row): NodeResult
    {
        switch ($row->status) {
            case WorkflowNodeExecution::COMPLETED:
                if ($row->node_type === WorkflowGraph::END) {
                    return NodeResult::end();
                }

                $resumeAt = $row->result['resume_at'] ?? null;

                return $resumeAt
                    ? NodeResult::waitUntil(Carbon::parse($resumeAt), $row->result)
                    : NodeResult::completed($row->result ?? [], $row->branch);

            case WorkflowNodeExecution::SKIPPED:
                return NodeResult::cancelled($row->error ?? 'Cancelled.');

            case WorkflowNodeExecution::FAILED:
                // A failure the workflow went on from ("on_error": "continue")
                if (!empty($row->result['continued'])) {
                    return NodeResult::failed($row->error ?? 'Failed.', [], true);
                }
                break;
        }

        return NodeResult::failed(
            "The step \"{$row->node_id}\" was interrupted. It is not executed again to avoid duplicated actions."
        );
    }

    protected function graph(int $versionId): WorkflowGraph
    {
        return $this->graphs[$versionId] ??= new WorkflowGraph(
            WorkflowVersion::query()->findOrFail($versionId, ['id', 'graph'])->graph
        );
    }

    /**
     * End an execution. $from restricts the transition to the given statuses.
     */
    protected function finish(WorkflowExecution $execution, string $status, ?string $error = null, ?array $from = null): void
    {
        $query = WorkflowExecution::query()->whereKey($execution->id);

        if ($from) {
            $query->whereIn('status', $from);
        }

        $query->update([
            'status' => $status,
            'error' => $error ? mb_substr($error, 0, 1000) : null,
            'finished_at' => now(),
            'locked_at' => null,
            'waiting_event' => null,
            'resume_at' => null,
            'updated_at' => now(),
        ]);
    }
}
