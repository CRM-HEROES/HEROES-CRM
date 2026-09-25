<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\Import\GoogleSheetSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GoogleSheetSyncRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Unlimited attempts: while the import is busy this job keeps
     * releasing itself (see handle()) until retryUntil() expires, instead
     * of failing after --tries.
     */
    public $tries = 0;

    public function __construct(public Import $import)
    {
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(15);
    }

    public function handle(GoogleSheetSyncer $syncer): void
    {
        $freshImport = $this->import->fresh();

        if (!$freshImport || $freshImport->source !== 'google_sheets' || !$freshImport->sync_enabled) {
            $syncer->clearRetryQueued($this->import);
            return;
        }

        if (!$freshImport->is_processing && !$syncer->hasBeenImportedOnce($freshImport)) {
            $syncer->clearRetryQueued($freshImport);
            return;
        }

        if ($freshImport->is_processing) {
            $staleReset = $syncer->clearStaleProcessingLockIfNeeded($freshImport);
            if (!$staleReset) {
                // Still busy: poll again shortly rather than re-queuing
                // through queueRetryIfBusy(), whose dedupe key (set by the
                // job that is running right now) would silently drop the
                // retry and lose the edit until the 5-minute fallback poll.
                $this->release(2);
                return;
            }
        }

        // Free the dedupe key *before* downloading: any edit made from now
        // on can queue a fresh retry, and edits made before are already
        // covered by the download below.
        $syncer->clearRetryQueued($freshImport);

        $syncer->sync($freshImport);
    }
}
