<?php

namespace App\Jobs\Workflow;

use App\Workflows\WorkflowEventWaker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * A prospect did something (answered, called, ...): wakes up the workflows
 * that were waiting for it. Off the request that reported the event.
 * Retry-safe: waking an execution is a conditional update.
 */
class WakeWorkflowExecutions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 3;

    public $backoff = [30, 120];

    public function __construct(public string $event, public int $prospectId)
    {
        $this->onQueue(config('workflows.queue'));
    }

    public function handle(WorkflowEventWaker $waker): void
    {
        $waker->wake($this->event, $this->prospectId);
    }
}
