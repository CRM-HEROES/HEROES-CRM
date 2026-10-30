<?php

namespace App\Jobs\Workflow;

use App\Workflows\WorkflowEvents;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

/**
 * The prospects of an import are inserted in bulk, which bypasses the model
 * events: this job reports them to the workflows once the import is over
 * (assigned and complete), in batches of ids by primary key.
 */
class DispatchImportedProspectTriggers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const BATCH = 500;

    public $tries = 3;

    public function __construct(public int $importId, public int $afterId = 0)
    {
        $this->onQueue(config('workflows.queue'));
    }

    public function handle(): void
    {
        $prospects = DB::table('prospects')
            ->where('import_id', $this->importId)
            ->where('id', '>', $this->afterId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->limit(self::BATCH)
            ->get(['id', 'project_id']);

        if ($prospects->isEmpty()) {
            return;
        }

        foreach ($prospects->groupBy('project_id') as $projectId => $group) {
            WorkflowEvents::prospectsCreated((int) $projectId, $group->pluck('id')->all());
        }

        if ($prospects->count() === self::BATCH) {
            self::dispatch($this->importId, $prospects->last()->id);
        }
    }
}
