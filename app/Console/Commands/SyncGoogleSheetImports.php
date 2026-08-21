<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Services\Import\GoogleSheetDownloader;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGoogleSheetImports extends Command
{
    protected $signature = 'app:sync-google-sheets-imports';
    protected $description = 'Re-download and re-process Google Sheets imports that have auto-sync enabled and whose sync interval has elapsed.';

    public function handle(GoogleSheetDownloader $downloader)
    {
        $imports = Import::query()
            ->where('source', 'google_sheets')
            ->where('sync_enabled', true)
            ->where('is_processing', false)
            ->whereNotNull('url')
            ->get();

        $synced = 0;

        foreach ($imports as $import) {
            if (!$this->isDue($import)) {
                continue;
            }

            if ($this->syncImport($import, $downloader)) {
                ++$synced;
            }
        }

        $this->info("Synchronisation Google Sheets terminée. {$synced} import(s) relancé(s).");

        return self::SUCCESS;
    }

    /**
     * Whether enough time has passed since the last sync (or the last
     * manual processing, for an import that has never auto-synced yet).
     */
    protected function isDue(Import $import): bool
    {
        $reference = $import->last_synced_at ?: $import->processed_at;

        if (!$reference) {
            return true;
        }

        $intervalMinutes = $import->sync_interval_minutes ?: 30;

        return $reference->addMinutes($intervalMinutes)->isPast();
    }

    /**
     * Re-download the sheet and, on success, hand off to the same
     * "is_processing = true" flow the manual "Re importer" button uses
     * (ImportObserver::launchOrStop dispatches ImportProspects, which
     * already knows how to update existing prospects and add new ones).
     */
    protected function syncImport(Import $import, GoogleSheetDownloader $downloader): bool
    {
        try {
            $spreadsheetId = $downloader->extractSpreadsheetId($import->url);
            $file = $downloader->download($spreadsheetId, $import->project->slug);
        } catch (\Throwable $e) {
            Log::warning('SyncGoogleSheetImports: failed to download sheet, will retry next run', [
                'import_id' => $import->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        $import->update([
            'path' => $file['path'],
            'size' => $file['size'],
            'last_synced_at' => Carbon::now(),
            'is_processing' => true,
        ]);

        return true;
    }
}
