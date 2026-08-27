<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Services\Import\GoogleSheetSyncer;
use Illuminate\Console\Command;

class SyncGoogleSheetImports extends Command
{
    protected $signature = 'app:sync-google-sheets-imports';
    protected $description = 'Re-download and re-process Google Sheets imports that have auto-sync enabled and whose sync interval has elapsed. Safety-net fallback for imports whose real-time webhook (Apps Script trigger) is missing, broken, or hasn\'t fired.';

    public function handle(GoogleSheetSyncer $syncer)
    {
        $imports = Import::query()
            ->where('source', 'google_sheets')
            ->where('sync_enabled', true)
            ->where('is_processing', false)
            ->whereNotNull('source_url')
            ->get();

        $synced = 0;

        foreach ($imports as $import) {
            if (!$syncer->isDue($import)) {
                continue;
            }

            if ($syncer->sync($import)) {
                ++$synced;
            }
        }

        $this->info("Synchronisation Google Sheets terminée. {$synced} import(s) relancé(s).");

        return self::SUCCESS;
    }
}
