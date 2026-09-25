<?php

namespace App\Services\Import;

use App\Jobs\GoogleSheetSyncRequest;
use App\Jobs\ImportProspects;
use App\Models\Import;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
    const FALLBACK_POLL_MINUTES = 2;

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
    /**
     * An import that has never been processed nor synced is still waiting
     * for the user to check the mapping and click "Importer". Auto-syncing
     * it before that would import the rows behind the user's back, and the
     * user's own first run would then insert them all a second time.
     */
    public function hasBeenImportedOnce(Import $import): bool
    {
        return (bool) ($import->processed_at || $import->last_synced_at);
    }

    public function isDue(Import $import): bool
    {
        if (!$this->hasBeenImportedOnce($import)) {
            return false;
        }

        // last_synced_at is cast to Carbon, but processed_at isn't (kept as
        // a plain string by the model), so it must be parsed explicitly.
        $reference = $import->last_synced_at ?: (
            $import->processed_at ? Carbon::parse($import->processed_at) : null
        );

        if (!$reference) {
            return true;
        }

        // This poll is only the safety net for edits the real-time webhook
        // didn't report (Apps Script "onEdit" doesn't fire for changes made
        // by an API/automation). An unchanged sheet costs almost nothing
        // now (row fingerprints), so it is capped at a couple of minutes
        // whatever interval the import was saved with.
        $intervalMinutes = min($import->sync_interval_minutes ?: self::FALLBACK_POLL_MINUTES, self::FALLBACK_POLL_MINUTES);

        return $reference->copy()->addMinutes($intervalMinutes)->isPast();
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
        $previousPath = $import->path;

        Import::withoutEvents(function () use ($import, $file) {
            $import->update([
                'path' => $file['path'],
                'size' => $file['size'],
                'last_synced_at' => Carbon::now(),
                'processed_at' => null,
                'is_processing' => true,
            ]);
        });

        // Every sync downloads a fresh copy; the previous one is never read
        // again (nothing is processing, see the callers), so don't let them
        // pile up on disk.
        if ($previousPath && $previousPath !== $file['path']) {
            try {
                Storage::disk('imports')->delete($previousPath);
            } catch (\Throwable $e) {
                Log::warning('GoogleSheetSyncer: could not delete previous sheet copy', [
                    'import_id' => $import->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->releaseSyncRequestClaim($import);

        try {
            ImportProspects::dispatch($import, true)->onQueue('imports');
        } catch (\Throwable $e) {
            // The import was already flagged as processing above: without
            // this it would stay locked until the stale-lock timeout.
            Import::withoutEvents(function () use ($import) {
                $import->forceFill(['is_processing' => false])->saveQuietly();
            });

            Log::error('GoogleSheetSyncer: could not dispatch the import job', [
                'import_id' => $import->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Schedule a retry when a sync is already running or a burst of edits
     * triggered multiple webhook calls in a short window. This prevents a
     * rapid spreadsheet edit storm from silently dropping the latest data
     * while the import worker is still processing the previous snapshot.
     */
    public function clearRetryQueued(Import $import): void
    {
        try {
            $store = Cache::store('redis');
            $store->get('google-sheet-sync-retry-probe');
        } catch (\Throwable $e) {
            $store = Cache::store();
        }

        $store->forget('google-sheet-sync-retry-queued-' . $import->id);
    }

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

        // Cleared by GoogleSheetSyncRequest as soon as it starts syncing;
        // the TTL is only a safety net if that job is lost.
        $store->put($retryKey, true, 900);

        $dispatch = GoogleSheetSyncRequest::dispatch($import)
            ->onQueue('imports');

        if ($delaySeconds > 0) {
            $dispatch->delay(now()->addSeconds($delaySeconds));
        }
    }
}
