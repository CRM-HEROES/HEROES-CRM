<?php

namespace App\Jobs\Workflow;

use App\Workflows\WorkflowEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Advances one workflow execution until it ends, waits or has run
 * a bounded number of nodes (then it hands over to a new job).
 *
 * Only the id is carried: the state is always read from the database, and
 * the engine claims the execution atomically, so a job that is duplicated,
 * retried or replayed by a restarted worker does nothing wrong.
 */
class RunWorkflowExecution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [30, 120];

    public function __construct(public int $executionId)
    {
        $this->onQueue(config('workflows.queue'));
    }

    public function handle(WorkflowEngine $engine): void
    {
        $engine->run($this->executionId);
    }

    /**
     * The job gave up (exceptions raised before the engine could record them)
     */
    public function failed(\Throwable $exception): void
    {
        app(WorkflowEngine::class)->abort($this->executionId, $exception->getMessage());
    }
}
