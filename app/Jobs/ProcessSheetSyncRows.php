<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\Import\SheetSyncProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Creates the prospects of the rows received from Google Sheets for one
 * import. Running it several times, or several copies at once, is harmless:
 * see SheetSyncProcessor.
 */
class ProcessSheetSyncRows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE = 'sheet-sync';

    public $tries = 5;
    public $timeout = 240;

    public function __construct(public int $importId)
    {
        $this->onQueue(self::QUEUE);
    }

    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    public function handle(SheetSyncProcessor $processor): void
    {
        $import = Import::withoutGlobalScopes()->find($this->importId);

        if (!$import) {
            return;
        }

        $processor->process($import);
    }
}
