<?php

namespace App\Services\Import;

use App\Jobs\GoogleSheetSyncRequest;
use App\Jobs\ImportProspects;
use App\Models\Import;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
    public function clearStaleProcessingLockIfNeeded(Import $import, int $staleMinutes = 10): bool
    {
        if (!$import->is_processing) {
            return false;
        }

        $updatedAt = $import->updated_at ?: $import->created_at;
        if (!$updatedAt) {
            return false;
        }

        if ($updatedAt->copy()->addMinutes($staleMinutes)->isFuture()) {
            return false;
        }

        Log::warning('GoogleSheetSyncer: resetting stale processing lock', [
            'import_id' => $import->id,
            'project_id' => $import->project_id,
            'updated_at' => $updatedAt->toDateTimeString(),
            'stale_minutes' => $staleMinutes,
        ]);

        Import::withoutEvents(function () use ($import) {
            $import->forceFill([
                'is_processing' => false,
                'processed_at' => null,
            ])->saveQuietly();
        });

        return true;
    }

    public function claimSyncRequest(Import $import, int $ttlSeconds = 15): bool
    {
        try {
            $store = Cache::store('redis');
            $store->get('google-sheet-sync-coalesce-probe');
        } catch (\Throwable $e) {
            $store = Cache::store();
        }

        $key = 'google-sheet-sync-coalesce-' . $import->id;
        $countKey = 'google-sheet-sync-coalesce-count-' . $import->id;

        if ($store->has($key)) {
            $coalescedCount = (int) ($store->get($countKey, 0)) + 1;
            $store->put($countKey, $coalescedCount, $ttlSeconds + 5);

            Log::info('GoogleSheetSyncer: coalesced duplicate sync trigger', [
                'import_id' => $import->id,
                'key' => $key,
                'ttl_seconds' => $ttlSeconds,
                'coalesced_count' => $coalescedCount,
            ]);

            return false;
        }

        $store->put($key, Carbon::now()->toDateTimeString(), $ttlSeconds);
        $store->put($countKey, 0, $ttlSeconds + 5);

        return true;
    }

    public function consumeCoalescedBurstCount(Import $import): int
    {
        try {
            $store = Cache::store('redis');
            $store->get('google-sheet-sync-coalesce-probe');
        } catch (\Throwable $e) {
            $store = Cache::store();
        }

        $countKey = 'google-sheet-sync-coalesce-count-' . $import->id;
        $count = (int) $store->get($countKey, 0);
        $store->forget($countKey);

        return $count;
    }

    public function releaseSyncRequestClaim(Import $import): void
    {
        try {
            $store = Cache::store('redis');
            $store->get('google-sheet-sync-coalesce-probe');
        } catch (\Throwable $e) {
            $store = Cache::store();
        }

        $store->forget('google-sheet-sync-coalesce-' . $import->id);
        $store->forget('google-sheet-sync-coalesce-count-' . $import->id);
    }

    public function sync(Import $import): bool
    {
        $this->clearStaleProcessingLockIfNeeded($import);
        $coalescedBurstCount = $this->consumeCoalescedBurstCount($import);

        Log::info('GoogleSheetSyncer: sync started', [
            'import_id' => $import->id,
            'project_id' => $import->project_id,
            'source_url' => $import->source_url,
            'coalesced_burst_count' => $coalescedBurstCount,
        ]);

        try {
            $spreadsheetId = $this->downloader->extractSpreadsheetId($import->source_url);
            $file = $this->downloader->download($spreadsheetId, $import->project->slug);
        } catch (\Throwable $e) {
            $this->releaseSyncRequestClaim($import);

            Log::warning('GoogleSheetSyncer: failed to download sheet, will retry next run', [
                'import_id' => $import->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        Log::info('GoogleSheetSyncer: spreadsheet downloaded successfully, dispatching import job', [
            'import_id' => $import->id,
            'file_path' => $file['path'],
            'file_size' => $file['size'],
        ]);

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

        $this->releaseSyncRequestClaim($import);

        ImportProspects::dispatch($import, true)->onQueue('imports');

        return true;
    }

    /**
     * Schedule a retry when a sync is already running or a burst of edits
     * triggered multiple webhook calls in a short window. This prevents a
     * rapid spreadsheet edit storm from silently dropping the latest data
     * while the import worker is still processing the previous snapshot.
     */
    public function queueRetryIfBusy(Import $import, int $delaySeconds = 0): void
    {
        try {
            $store = Cache::store('redis');
            $store->get('google-sheet-sync-retry-probe');
        } catch (\Throwable $e) {
            $store = Cache::store();
        }

        $retryKey = 'google-sheet-sync-retry-queued-' . $import->id;

        if ($store->has($retryKey)) {
            return;
        }

        $store->put($retryKey, true, 60);

        $dispatch = GoogleSheetSyncRequest::dispatch($import)
            ->onQueue('imports');

        if ($delaySeconds > 0) {
            $dispatch->delay(now()->addSeconds($delaySeconds));
        }
    }
}
