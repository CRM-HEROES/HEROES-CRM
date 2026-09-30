<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSheetSyncRows;
use App\Models\ImportSyncRow;
use App\Services\Import\SheetSyncProcessor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SheetSyncRecover extends Command
{
    protected $signature = 'sheet-sync:recover
        {--retry-failed : Also put rows in "failed" status back to pending}
        {--sync : Process the pending rows right now instead of queueing them}';

    protected $description = 'Recovers Google Sheets instant-sync rows left behind by a crash, a restart or an unavailable queue.';

    public function handle(SheetSyncProcessor $processor)
    {
        $recovered = $processor->recoverStaleClaims();

        if ($this->option('retry-failed')) {
            DB::table('import_sync_rows')
                ->where('status', ImportSyncRow::STATUS_FAILED)
                ->whereNotNull('payload')
                ->update(['status' => ImportSyncRow::STATUS_PENDING, 'attempts' => 0, 'updated_at' => Carbon::now()]);
        }

        // Pending rows nobody is working on: dispatching again is harmless
        // (rows are claimed atomically), so err on the side of dispatching.
        $importIds = DB::table('import_sync_rows')
            ->where('status', ImportSyncRow::STATUS_PENDING)
            ->where('created_at', '<', Carbon::now()->subSeconds(20))
            ->distinct()
            ->pluck('import_id');

        foreach ($importIds as $importId) {
            if ($this->option('sync')) {
                $import = \App\Models\Import::withoutGlobalScopes()->find($importId);
                $import && $processor->process($import);
            } else {
                ProcessSheetSyncRows::dispatch((int) $importId);
            }
        }

        $this->info("Google Sheets sync : {$recovered} ligne(s) récupérée(s), {$importIds->count()} import(s) relancé(s).");

        return self::SUCCESS;
    }
}
