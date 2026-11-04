<?php

namespace App\Jobs\Agent;

use App\Agents\AgentRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs one turn of an agent. It is never retried by the queue: the runner
 * stores its progress, and a run stopped by a crash is picked up again by
 * agents:resume without ever repeating a write.
 */
class RunAgentTurn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    // A model call can last minutes; a run holds at most max_iterations of them
    public $timeout = 1800;

    public function __construct(public int $runId)
    {
        $this->onQueue(config('agents.queue'));
    }

    public function handle(AgentRunner $runner): void
    {
        $runner->run($this->runId);
    }

    public function failed(?\Throwable $exception): void
    {
        app(AgentRunner::class)->abort($this->runId, "L'exécution de l'agent a été interrompue.");
    }
}
