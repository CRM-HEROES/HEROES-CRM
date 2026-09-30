<?php

namespace App\Services\Import;

use App\Jobs\ImportProspects;
use App\Models\Import;
use App\Models\ImportSyncRow;
use App\Services\ProspectAutoAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Turns the rows stored in import_sync_rows (the webhook "inbox") into
 * prospects. Safe to run from several workers and several times:
 *
 *  - rows are claimed with one atomic UPDATE, so two workers never get the same
 *    rows;
 *  - creation happens while holding the project deduplication lock (the same
 *    one ImportProspects::createProspects() uses, MySQL locks being re-entrant)
 *    and inside one transaction together with the status update, so a row is
 *    either created AND marked, or neither;
 *  - only new leads are inserted: an existing prospect is never modified.
 */
class SheetSyncProcessor
{
    public const BATCH_SIZE = 200;

    /** A claim older than this is considered abandoned (worker killed, restart...). */
    public const STALE_CLAIM_MINUTES = 5;

    /**
     * Process pending rows of an import until none is left (or $maxBatches reached).
     *
     * @return array{created:int, skipped:int}
     */
    public function process(Import $import, int $maxBatches = 50): array
    {
        $totals = ['created' => 0, 'skipped' => 0];

        for ($i = 0; $i < $maxBatches; $i++) {
            $rows = $this->claim($import->id);

            if ($rows->isEmpty()) {
                break;
            }

            $result = $this->processClaimed($import, $rows);
            $totals['created'] += $result['created'];
            $totals['skipped'] += $result['skipped'];
        }

        return $totals;
    }

    /**
     * Atomically move up to BATCH_SIZE pending rows to "processing".
     */
    public function claim(int $importId, int $limit = self::BATCH_SIZE)
    {
        $token = (string) Str::uuid();

        $claimed = DB::table('import_sync_rows')
            ->where('import_id', $importId)
            ->where('status', ImportSyncRow::STATUS_PENDING)
            ->orderBy('id')
            ->limit($limit)
            ->update([
                'status' => ImportSyncRow::STATUS_PROCESSING,
                'claim_token' => $token,
                'claimed_at' => Carbon::now(),
                'attempts' => DB::raw('attempts + 1'),
            ]);

        if ($claimed === 0) {
            return collect();
        }

        return ImportSyncRow::where('import_id', $importId)
            ->where('claim_token', $token)
            ->where('status', ImportSyncRow::STATUS_PROCESSING)
            ->orderBy('id')
            ->get();
    }

    protected function processClaimed(Import $import, $rows): array
    {
        $import = Import::withoutGlobalScopes()->findOrFail($import->id);
        $lockName = 'heroes-crm-prospect-project-' . $import->project_id;
        $lockAcquired = false;
        $createdIds = [];

        try {
            // Same lock as ImportProspects::createProspects(); taken *before*
            // the transaction so its snapshot already sees every prospect
            // committed by the previous lock holder.
            $lock = DB::selectOne('SELECT GET_LOCK(?, 120) AS acquired', [$lockName]);
            $lockAcquired = (int) ($lock->acquired ?? 0) === 1;

            if (!$lockAcquired) {
                throw new \RuntimeException('Unable to acquire the prospect deduplication lock.');
            }

            $items = $rows->map(fn (ImportSyncRow $row) => [
                'key' => $row->external_id,
                'headers' => $row->payload['headers'] ?? [],
                'values' => $row->payload['values'] ?? [],
            ])->all();

            $results = DB::transaction(function () use ($import, $items, $rows) {
                $results = (new ImportProspects($import))->syncRows($items);
                $this->markProcessed($rows, $results);

                return $results;
            });

            foreach ($results as $result) {
                if ($result['status'] === 'created') {
                    $createdIds[] = $result['prospect_id'];
                }
            }
        } catch (\Throwable $exception) {
            $this->release($rows, $exception);

            throw $exception;
        } finally {
            if ($lockAcquired) {
                DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
            }
        }

        if ($createdIds) {
            // Same automatic assignment as a normal import (roles, users,
            // phone country codes), restricted to the prospects just created.
            try {
                app(ProspectAutoAssignment::class)
                    ->assignUnassignedProspects(null, $import->id, $createdIds);
            } catch (\Throwable $exception) {
                // The prospects are saved; the scheduled assignment command
                // picks up anything that stayed unassigned.
                Log::warning('SheetSync: automatic assignment failed', [
                    'import_id' => $import->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        Import::withoutEvents(function () use ($import) {
            DB::table('imports')->where('id', $import->id)->update([
                'last_synced_at' => Carbon::now(),
                'rows_count' => DB::table('prospects')
                    ->where('import_id', $import->id)
                    ->whereNull('deleted_at')
                    ->count(),
            ]);
        });

        return [
            'created' => count($createdIds),
            'skipped' => count($results) - count($createdIds),
        ];
    }

    /**
     * Record the outcome of each row and drop its payload (personal data is
     * only kept as long as it is needed).
     */
    protected function markProcessed($rows, array $results): void
    {
        $now = Carbon::now();

        foreach ($rows as $row) {
            $result = $results[$row->external_id] ?? ['status' => 'skipped', 'reason' => 'not_processed'];

            DB::table('import_sync_rows')
                ->where('id', $row->id)
                ->where('claim_token', $row->claim_token)
                ->update([
                    'status' => $result['status'],
                    'prospect_id' => $result['prospect_id'] ?? null,
                    'reason' => $result['reason'] ?? null,
                    'error' => null,
                    'payload' => null,
                    'processed_at' => $now,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Give the rows back after a failure: pending again, or failed for good
     * once MAX_ATTEMPTS is reached.
     */
    protected function release($rows, \Throwable $exception): void
    {
        Log::warning('SheetSync: batch failed, rows released', [
            'rows' => $rows->count(),
            'message' => $exception->getMessage(),
        ]);

        $message = Str::limit($exception->getMessage(), 500, '');

        foreach ($rows->chunk(500) as $chunk) {
            DB::table('import_sync_rows')
                ->whereIn('id', $chunk->pluck('id')->all())
                ->where('status', ImportSyncRow::STATUS_PROCESSING)
                ->update([
                    'status' => DB::raw("IF(attempts >= " . ImportSyncRow::MAX_ATTEMPTS . ", 'failed', 'pending')"),
                    'error' => $message,
                    'claim_token' => null,
                    'claimed_at' => null,
                    'updated_at' => Carbon::now(),
                ]);
        }
    }

    /**
     * Rows whose worker disappeared (crash, restart) go back to pending.
     *
     * @return int number of rows recovered
     */
    public function recoverStaleClaims(): int
    {
        return DB::table('import_sync_rows')
            ->where('status', ImportSyncRow::STATUS_PROCESSING)
            ->where('claimed_at', '<', Carbon::now()->subMinutes(self::STALE_CLAIM_MINUTES))
            ->update([
                'status' => DB::raw("IF(attempts >= " . ImportSyncRow::MAX_ATTEMPTS . ", 'failed', 'pending')"),
                'claim_token' => null,
                'claimed_at' => null,
                'updated_at' => Carbon::now(),
            ]);
    }
}
