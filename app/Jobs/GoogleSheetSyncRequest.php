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

    public function __construct(public Import $import)
    {
    }

    public function handle(GoogleSheetSyncer $syncer): void
    {
        $freshImport = $this->import->fresh();

        if (!$freshImport || $freshImport->source !== 'google_sheets' || !$freshImport->sync_enabled) {
            return;
        }

        if ($freshImport->is_processing) {
            $syncer->queueRetryIfBusy($freshImport, 15);

            return;
        }

        $syncer->sync($freshImport);
    }
}
