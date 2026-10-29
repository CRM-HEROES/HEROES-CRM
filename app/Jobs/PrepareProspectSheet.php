<?php

namespace App\Jobs;

use App\Exports\ProspectDownload;
use App\Exports\ProspectSheet;
use App\Models\Export;
use App\Models\GoogleAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class PrepareProspectSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;
    public bool $failOnTimeout = true;

    public function __construct(public Export $export, public GoogleAccount $account, public string $title)
    {
    }

    public function handle(ProspectSheet $sheet): void
    {
        $previousUser = Auth::user();
        $previousInput = request()->all();
        $previousTeam = getPermissionsTeamId();

        try {
            $download = app(ProspectDownload::class);
            $details = array_intersect_key($download->status($this->export), array_flip(['sheet_id', 'sheet_url']));
            $download->setStatus($this->export, 'processing', ['mode' => 'sheet'] + $details);
            $creator = $this->export->creator;

            if (!$creator || !$this->export->project) {
                throw new RuntimeException('Le créateur ou le projet de l’export est introuvable.');
            }
            if ($this->account->trashed() || $this->account->for !== 'drive' || $this->account->project_id != $this->export->project_id) {
                throw new RuntimeException(ProspectSheet::CONNECTION_MESSAGE);
            }

            Auth::setUser($creator);
            setPermissionsTeamId($this->export->project_id);
            request()->merge(['project' => $this->export->project]);

            if (!$creator->can('prospectExport', $this->export->project)) {
                throw new RuntimeException('Vous ne pouvez plus exporter les prospects de ce projet.');
            }

            $sheet->prepare($this->export, $this->account, $this->title);
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

    public function failed(?Throwable $exception): void
    {
        $download = app(ProspectDownload::class);
        $details = array_intersect_key($download->status($this->export), array_flip(['sheet_id', 'sheet_url']));
        $message = $exception?->getMessage() === ProspectSheet::CONNECTION_MESSAGE
            ? ProspectSheet::CONNECTION_MESSAGE
            : ProspectSheet::FAILURE_MESSAGE;
        $download->setStatus($this->export, 'failed', [
            'mode' => 'sheet',
            'message' => $message,
        ] + $details);
    }
}
