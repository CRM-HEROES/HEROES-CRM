<?php

namespace App\Console\Commands;

use App\Workflows\TimeTriggerScanner;
use App\Workflows\WorkflowExecutionManager;
use Illuminate\Console\Command;

class WorkflowResume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:resume {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resume the workflow executions that are due, and recover the stuck ones';

    /**
     * Execute the console command.
     *
     * Two indexed queries, however many prospects or workflows exist:
     * only the executions that are due are touched.
     */
    public function handle(WorkflowExecutionManager $executions, TimeTriggerScanner $clock)
    {
        // Appointments that start / end: range queries, no scan
        $triggered = $clock->scan();
        $recovered = $executions->recoverStuck();
        $dispatched = $executions->resumeDue((int) ($this->option('limit') ?: config('workflows.sweep_limit')));

        if ($triggered || $recovered || $dispatched) {
            $this->info("Workflows: $triggered started by the clock, $dispatched resumed, $recovered recovered.");
        }
    }
}
