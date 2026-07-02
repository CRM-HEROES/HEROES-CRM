<?php

namespace App\Jobs;

use App\Exports\ProspectExport;
use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExportProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Export $export;
    protected $summaryDataCount = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(Export $export)
    {
        $this->export = $export;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $take = 1000;
        $skip = 0;
        $page = 1;

        // Chunk data
        do {
            if ($this->export->count && $this->export->count == $skip) {
                break;
            }
            
            $exportCollection = new ProspectExport($this->export, $this->export->count ? min($take, $this->export->count - $skip) : $take, $skip);

            Excel::store($exportCollection, $this->export->id . '-' . $page . '.xlsx', 'exports');
            $skip += $take;
            ++$page;
        } while ($exportCollection->getCount() >= $take);

        // Notify export finished
        $this->notifyExportFinished();
    }

    /**
     * Export finished Notification
     * For the creator of the export
     */
    protected function notifyExportFinished()
    {
        Mail::to($this->export->email ? $this->export->email : $this->export->creator->email)->send(new \App\Notifications\ExportFinished($this->export));
    	/*$this->export->creator->notify(
    		new \App\Notifications\ExportFinished($this->export)
    	);*/
    }
}
