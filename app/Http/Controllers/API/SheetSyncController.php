<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSheetSyncRows;
use App\Models\Import;
use App\Models\ImportSyncRow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Webhook called by the Google Apps Script installed in a synced sheet.
 *
 * It receives the new rows themselves (never just a "something changed"
 * signal), stores them durably and answers 202. Creating the prospects is done
 * afterwards by a queued job. The unique key (import_id, external_id) makes the
 * endpoint idempotent, including for identical requests arriving at the same
 * time.
 */
class SheetSyncController extends Controller
{
    public const MAX_ROWS_PER_REQUEST = 500;

    public function store(Request $request, Import $import)
    {
        $import->makeVisible('token');

        // The body is signed with the import's token (HMAC-SHA256, hex), so
        // the secret never travels in the URL or in the logs.
        $expected = hash_hmac('sha256', (string) $request->getContent(), (string) $import->token);
        $signature = (string) $request->header('X-Signature', '');

        if ($import->token === null || !hash_equals($expected, $signature)) {
            return $this->reject($import, 403, 'Signature invalide.');
        }

        if ($import->source !== 'google_sheets' || !$import->sync_enabled) {
            return $this->reject($import, 409, "La synchronisation instantanée n'est pas activée pour cet import.");
        }

        if (!$import->headers || !$import->mapping) {
            return $this->reject($import, 409, "Le mapping de l'import n'est pas configuré.");
        }

        $data = json_decode((string) $request->getContent(), true);
        $rows = is_array($data) ? ($data['rows'] ?? null) : null;

        if (!is_array($rows) || !is_array($data['headers'] ?? null)) {
            return $this->reject($import, 422, 'Requête invalide.');
        }

        if (count($rows) > self::MAX_ROWS_PER_REQUEST) {
            return $this->reject($import, 413, 'Trop de lignes dans une requête (max ' . self::MAX_ROWS_PER_REQUEST . ').');
        }

        $now = Carbon::now();
        $sheet = isset($data['sheet']) ? Str::limit((string) $data['sheet'], 191, '') : null;
        $batchId = isset($data['batch_id']) ? Str::limit((string) $data['batch_id'], 64, '') : null;
        $headers = array_values($data['headers']);
        $records = [];

        foreach ($rows as $row) {
            $key = is_array($row) ? trim((string) ($row['key'] ?? '')) : '';

            if ($key === '' || !is_array($row['values'] ?? null)) {
                return $this->reject($import, 422, 'Ligne invalide (clé ou valeurs manquantes).');
            }

            $records[] = [
                'import_id' => $import->id,
                'external_id' => strlen($key) > 191 ? 'sha1:' . sha1($key) : $key,
                'sheet' => $sheet,
                'batch_id' => $batchId,
                'payload' => json_encode(['headers' => $headers, 'values' => array_values($row['values'])]),
                'status' => ImportSyncRow::STATUS_PENDING,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // One row per lead, guaranteed by the unique key: a lead that is
        // already stored (being processed or done) is left strictly as it is.
        // Affected rows = leads really stored now; the others are replays.
        $stored = 0;
        foreach (array_chunk($records, 200) as $chunk) {
            $stored += DB::table('import_sync_rows')->insertOrIgnore($chunk);
        }

        if ($stored > 0) {
            $this->dispatchProcessing($import);
        }

        Log::info('SheetSync: batch received', [
            'import_id' => $import->id,
            'sheet' => $sheet,
            'received' => count($records),
            'new' => $stored,
        ]);

        return response()->json([
            'batch_id' => $batchId,
            'received' => count($records),
            'new' => $stored,
            'duplicates' => count($records) - $stored,
        ], 202);
    }

    /**
     * Rejected request: answer and leave a trace in the logs (why, for which
     * import), since the caller is a script running on Google's side.
     */
    protected function reject(Import $import, int $status, string $message)
    {
        Log::warning('SheetSync: request rejected', [
            'import_id' => $import->id,
            'status' => $status,
            'reason' => $message,
        ]);

        return response()->json(['message' => $message], $status);
    }

    /**
     * The rows are already safe in MySQL: if the queue backend is down the
     * request must still succeed. `sheet-sync:recover` dispatches the work
     * later.
     */
    protected function dispatchProcessing(Import $import): void
    {
        try {
            ProcessSheetSyncRows::dispatch($import->id);
        } catch (\Throwable $exception) {
            Log::warning('SheetSync: could not queue processing, left to the recovery command', [
                'import_id' => $import->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
