<?php

namespace App\Jobs\Agent;

use App\Agents\ApprovalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs an action a human approved. Never retried by the queue: the service
 * claims the action before running it, so a write is not repeated.
 */
class ExecuteAgentAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    // A bulk action works through its chunks
    public $timeout = 3600;

    public function __construct(public int $actionId)
    {
        $this->onQueue(config('agents.queue'));
    }

    public function handle(ApprovalService $approvals): void
    {
        $approvals->execute($this->actionId);
    }
}
