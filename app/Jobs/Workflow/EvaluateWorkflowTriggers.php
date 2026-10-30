<?php

namespace App\Jobs\Workflow;

use App\Workflows\FilterTransitionDetector;
use App\Workflows\WorkflowSubject;
use App\Workflows\WorkflowTriggerResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Handles, off the request, what a change of a prospect / order / appointment
 * means for the workflows: starts the "created" and "updated" ones and detects
 * the filter transitions. One job can carry a batch of ids (imports).
 *
 * Retry-safe: executions are idempotent per trigger occurrence, and the
 * filter transitions are owned by whoever records them.
 */
class EvaluateWorkflowTriggers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 3;

    public $backoff = [30, 120];

    /**
     * @param  int[]  $ids
     * @param  string  $event  "created" | "updated"
     * @param  int  $stamp  time of the change (identifies an occurrence of "updated")
     */
    public function __construct(
        public string $subjectType,
        public array $ids,
        public int $projectId,
        public string $event,
        public int $stamp,
    ) {
        $this->onQueue(config('workflows.queue'));
    }

    public function handle(WorkflowTriggerResolver $resolver, FilterTransitionDetector $detector): void
    {
        foreach ($this->ids as $id) {
            if ($this->event === 'created') {
                $resolver->fire("{$this->subjectType}.created", $this->projectId, $id, "{$this->subjectType}.created:$id");
            } else {
                // Changes within the same second are one occurrence
                $resolver->fire("{$this->subjectType}.updated", $this->projectId, $id, "{$this->subjectType}.updated:$id:{$this->stamp}");
            }
        }

        if ($this->subjectType === WorkflowSubject::PROSPECT) {
            $detector->detect($this->projectId, $this->subjectType, $this->ids);
        }
    }
}
