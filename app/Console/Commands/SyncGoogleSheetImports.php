<?php

namespace App\Console\Commands;

use App\Jobs\ImportProspects;
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
            ->whereNotNull('source_url')
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
        // last_synced_at is cast to Carbon, but processed_at isn't (kept as
        // a plain string by the model), so it must be parsed explicitly.
        $reference = $import->last_synced_at ?: (
            $import->processed_at ? Carbon::parse($import->processed_at) : null
        );

        if (!$reference) {
            return true;
        }

        $intervalMinutes = $import->sync_interval_minutes ?: 30;

        return $reference->addMinutes($intervalMinutes)->isPast();
    }

    /**
     * Re-download the sheet and, on success, run an *incremental*
     * ImportProspects pass: existing prospects (already imported by this
     * same import on a previous sync) are recognised via
     * existingEmails/existingMobiles and skipped or have their meta
     * merged if it changed, instead of being wiped and recreated — only
     * genuinely new rows get inserted. See ImportProspects's $incremental
     * flag for the details.
     */
    protected function syncImport(Import $import, GoogleSheetDownloader $downloader): bool
    {
        try {
            $spreadsheetId = $downloader->extractSpreadsheetId($import->source_url);
            $file = $downloader->download($spreadsheetId, $import->project->slug);
        } catch (\Throwable $e) {
            Log::warning('SyncGoogleSheetImports: failed to download sheet, will retry next run', [
                'import_id' => $import->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        // Bypass ImportObserver here: flipping is_processing through a
        // normal update() would make it dispatch its own *non-incremental*
        // ImportProspects run (ImportObserver::launchOrStop), which wipes
        // and recreates every prospect of the import on every sync. The
        // incremental run is dispatched explicitly below instead.
        Import::withoutEvents(function () use ($import, $file) {
            $import->update([
                'path' => $file['path'],
                'size' => $file['size'],
                'last_synced_at' => Carbon::now(),
                'processed_at' => null,
                'is_processing' => true,
            ]);
        });

        ImportProspects::dispatch($import, true)->onQueue('imports');

        return true;
    }
}
