<?php

namespace App\Workflows;

use App\Jobs\Workflow\RunWorkflowExecution;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Database\QueryException;

/**
 * Creates the executions and keeps them moving (resume, recovery, cancel).
 */
class WorkflowExecutionManager
{
    /**
     * Start a workflow for a subject.
     *
     * Idempotent: $triggerKey identifies the occurrence of the trigger
     * ("prospect.created:123"). The unique key (workflow_id, trigger_key)
     * makes the database refuse a second execution for it, whatever the
     * number of times the event is received. Returns null in that case.
     */
    public function start(
        int $projectId,
        int $workflowId,
        int $versionId,
        string $subjectType,
        int $subjectId,
        string $triggerKey,
        array $context = []
    ): ?WorkflowExecution {
        try {
            $execution = WorkflowExecution::create([
                'project_id' => $projectId,
                'workflow_id' => $workflowId,
                'workflow_version_id' => $versionId,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'status' => WorkflowExecution::PENDING,
                'trigger_key' => strlen($triggerKey) > 150 ? sha1($triggerKey) : $triggerKey,
                'context' => $context ?: null,
                'started_at' => now(),
            ]);
        } catch (QueryException $e) {
            if (self::isDuplicate($e)) {
                return null;
            }

            throw $e;
        }

        // The state is persisted first: a job that is lost is
        // recovered by the resume sweep.
        RunWorkflowExecution::dispatch($execution->id)->afterCommit();

        return $execution;
    }

    /**
     * Dispatch the executions that are due: waiting ones whose delay is over,
     * and pending ones whose job seems lost (not touched for a minute).
     * Only executions of active workflows are picked up: the ones of a paused
     * workflow stay where they are until it is activated again.
     */
    public function resumeDue(int $limit): int
    {
        $dispatched = 0;
        $now = now();

        WorkflowExecution::query()
            ->select('workflow_executions.id')
            ->join('workflows', 'workflows.id', '=', 'workflow_executions.workflow_id')
            ->where('workflows.status', Workflow::ACTIVE)
            ->whereNull('workflows.deleted_at')
            ->where(function ($query) use ($now) {
                $query
                    ->where(function ($query) use ($now) {
                        $query
                            ->where('workflow_executions.status', WorkflowExecution::WAITING)
                            ->where('workflow_executions.resume_at', '<=', $now);
                    })
                    ->orWhere(function ($query) use ($now) {
                        $query
                            ->where('workflow_executions.status', WorkflowExecution::PENDING)
                            ->where('workflow_executions.updated_at', '<=', $now->copy()->subMinute());
                    });
            })
            ->orderBy('workflow_executions.id')
            ->limit($limit)
            ->pluck('workflow_executions.id')
            ->each(function ($id) use (&$dispatched) {
                RunWorkflowExecution::dispatch($id);
                $dispatched++;
            });

        return $dispatched;
    }

    /**
     * Executions "running" for too long (worker killed) are made pending
     * again. The engine then fails the step that was interrupted instead
     * of running it twice.
     */
    public function recoverStuck(): int
    {
        return WorkflowExecution::query()
            ->where('status', WorkflowExecution::RUNNING)
            ->where('locked_at', '<=', now()->subMinutes(config('workflows.stuck_after_minutes')))
            ->update([
                'status' => WorkflowExecution::PENDING,
                'locked_at' => null,
                'updated_at' => now()->subMinutes(5),
            ]);
    }

    /**
     * Cancel the executions of a workflow that are not over.
     */
    public function cancelAll(int $workflowId, string $reason): int
    {
        return WorkflowExecution::query()
            ->where('workflow_id', $workflowId)
            ->whereIn('status', [WorkflowExecution::PENDING, WorkflowExecution::WAITING])
            ->update([
                'status' => WorkflowExecution::CANCELLED,
                'error' => $reason,
                'finished_at' => now(),
                'resume_at' => null,
                'waiting_event' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * Is this exception a unique key violation?
     * (and not another integrity error such as a missing foreign key)
     */
    public static function isDuplicate(QueryException $e): bool
    {
        return ($e->errorInfo[1] ?? null) == 1062
            || str_contains($e->getMessage(), 'UNIQUE constraint failed');
    }
}
