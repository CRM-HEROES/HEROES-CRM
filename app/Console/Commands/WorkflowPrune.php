<?php

namespace App\Console\Commands;

use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use Illuminate\Console\Command;

class WorkflowPrune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:prune {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the old workflow executions that are over, with their steps';

    /**
     * Execute the console command.
     *
     * Finished executions older than the retention (completed, cancelled,
     * skipped), and twice as old for the failed ones, which are worth keeping
     * longer to understand what went wrong. Executions in progress are never
     * touched. Deleted by batches of ids so that no statement locks the table
     * for long.
     */
    public function handle()
    {
        $days = (int) ($this->option('days') ?: config('workflows.retention_days'));
        $cutoff = now()->subDays($days);
        $failedCutoff = now()->subDays($days * 2);
        $total = 0;

        do {
            $ids = WorkflowExecution::query()
                ->where(function ($query) use ($cutoff, $failedCutoff) {
                    $query
                        ->where(function ($query) use ($cutoff) {
                            $query
                                ->whereIn('status', [WorkflowExecution::COMPLETED, WorkflowExecution::CANCELLED, WorkflowExecution::SKIPPED])
                                ->where('finished_at', '<', $cutoff);
                        })
                        ->orWhere(function ($query) use ($failedCutoff) {
                            $query
                                ->where('status', WorkflowExecution::FAILED)
                                ->where('finished_at', '<', $failedCutoff);
                        });
                })
                ->orderBy('id')
                ->limit(1000)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            // Explicitly: the foreign key cascade is not available on every database
            WorkflowNodeExecution::query()->whereIn('execution_id', $ids)->delete();
            WorkflowExecution::query()->whereIn('id', $ids)->delete();

            $total += $ids->count();
        } while ($ids->count() === 1000);

        if ($total) {
            $this->info("Workflows: $total old execution(s) deleted.");
        }
    }
}
