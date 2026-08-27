<?php

namespace App\Services\Import;

use App\Jobs\ImportProspects;
use App\Models\Import;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Re-downloads a Google Sheets import's source file and runs an
 * *incremental* ImportProspects pass on it. Shared by the periodic
 * SyncGoogleSheetImports command (polling, every 5 minutes) and the
 * real-time sync webhook (WebserviceController::syncGoogleSheet, called by
 * an Apps Script trigger the moment the sheet is edited) — both trigger the
 * exact same processing, only how often/when they call sync() differs.
 */
class GoogleSheetSyncer
{
    public function __construct(protected GoogleSheetDownloader $downloader)
    {
    }

    /**
     * Whether enough time has passed since the last sync (or the last
     * manual processing, for an import that has never auto-synced yet).
     * Used by the polling command; the webhook path ignores this and syncs
     * immediately (it has its own short cooldown instead, see
     * WebserviceController::syncGoogleSheet).
     */
    public function isDue(Import $import): bool
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
    public function sync(Import $import): bool
    {
        try {
            $spreadsheetId = $this->downloader->extractSpreadsheetId($import->source_url);
            $file = $this->downloader->download($spreadsheetId, $import->project->slug);
        } catch (\Throwable $e) {
            Log::warning('GoogleSheetSyncer: failed to download sheet, will retry next run', [
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
