<?php

namespace App\Workflows;

use App\Jobs\Workflow\RunWorkflowExecution;
use App\Models\WorkflowExecution;

/**
 * Wakes up the executions that wait for an event of a prospect
 * ("wait for an event" node).
 *
 * One indexed query (waiting_event, subject_type, subject_id) finds them, then
 * each one is claimed with a conditional UPDATE: whoever clears
 * `waiting_event` first owns the wake-up, so an event reported twice (or by
 * two workers) wakes an execution once. The execution is then run at once and
 * takes the "yes" way (the timeout would have left waiting_event set, which is
 * how the node tells the two apart).
 */
class WorkflowEventWaker
{
    /**
     * @return int number of executions woken up
     */
    public function wake(string $event, int $prospectId): int
    {
        $woken = 0;

        $ids = WorkflowExecution::query()
            ->where('waiting_event', $event)
            ->where('subject_type', WorkflowSubject::PROSPECT)
            ->where('subject_id', $prospectId)
            ->where('status', WorkflowExecution::WAITING)
            ->pluck('id');

        foreach ($ids as $id) {
            $claimed = WorkflowExecution::query()
                ->whereKey($id)
                ->where('waiting_event', $event)
                ->where('status', WorkflowExecution::WAITING)
                ->update([
                    'waiting_event' => null,
                    'resume_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($claimed) {
                RunWorkflowExecution::dispatch($id);
                $woken++;
            }
        }

        return $woken;
    }
}
