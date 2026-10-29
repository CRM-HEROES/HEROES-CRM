<?php

namespace App\Jobs;

use App\Exports\ProspectExport;
use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class ExportProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected const CHUNK_SIZE = 250;

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
        $take = static::CHUNK_SIZE;
        $skip = 0;
        $page = 1;
        $previousUser = Auth::user();
        $previousInput = request()->all();
        $previousTeam = getPermissionsTeamId();

        try {
            $creator = $this->export->creator;

            if (!$creator || !$this->export->project) {
                throw new RuntimeException('Le créateur ou le projet de l’export est introuvable.');
            }

            // Timeline visibility uses the same user and project as the export request.
            Auth::setUser($creator);
            setPermissionsTeamId($this->export->project_id);
            request()->merge(['project' => $this->export->project]);

            // Chunk data
            do {
                if ($this->export->count && $this->export->count == $skip) {
                    break;
                }

                $limit = $this->export->count ? min($take, $this->export->count - $skip) : $take;
                $filename = $this->export->id . '-' . $page . '.xlsx';

                $exportCollection = new ProspectExport($this->export, $limit, $skip);

                if (Excel::store($exportCollection, $filename, 'exports') === false) {
                    throw new RuntimeException('Impossible d’enregistrer le fichier d’export.');
                }

                $exportedCount = $exportCollection->getCount();

                unset($exportCollection);
                gc_collect_cycles();

                $skip += $take;
                ++$page;
            } while ($exportedCount >= $take);

            // Notify export finished
            $this->notifyExportFinished();

        } finally {
            if ($previousUser) {
                Auth::setUser($previousUser);
            } else {
                Auth::forgetUser();
            }

            setPermissionsTeamId($previousTeam);
            request()->replace($previousInput);
        }
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
