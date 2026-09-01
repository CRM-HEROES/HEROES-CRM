<?php

namespace App\Jobs\Doctolib;

use App\Models\DoctolibAccount;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Fetches appointments for one Doctolib account and imports them as
 * Events (see doctolib_calendar for the target Calendar, doctolib_event
 * for the distant-id mapping that prevents duplicate imports).
 *
 * NOT IMPLEMENTED YET: this needs one of two data sources that aren't
 * available in this environment yet, pick whichever access is granted:
 *
 *  - Doctolib partner REST API (requires an official partner agreement):
 *    call the appointments endpoint with $doctolibAccount->token, map
 *    each appointment to Event fields (name, description, location,
 *    started_at, ended_at), upsert via the doctolib_event pivot on the
 *    distant appointment id.
 *
 *  - Doctolib ICS calendar feed (private per-agenda export URL, no
 *    partner agreement needed, read-only, no realtime/webhooks):
 *    store the feed URL on DoctolibAccount, fetch it over HTTP, parse
 *    VEVENT blocks (UID, SUMMARY, DESCRIPTION, LOCATION, DTSTART,
 *    DTEND), same upsert logic via doctolib_event.
 *
 * Once real data can be fetched, follow the same shape as
 * App\Jobs\Pipedrive\PersonsGet: dedupe against the pivot table before
 * inserting, then map + insert the new rows.
 */
class AppointmentsGet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected DoctolibAccount $doctolibAccount;
    protected Project $project;

    /**
     * Create a new job instance.
     */
    public function __construct(DoctolibAccount $doctolibAccount, Project $project)
    {
        $this->doctolibAccount = $doctolibAccount;
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::warning('Doctolib\AppointmentsGet dispatched but not implemented yet: no Doctolib API/ICS access configured.', [
            'doctolib_account_id' => $this->doctolibAccount->id,
            'project_id' => $this->project->id,
        ]);
    }
}
