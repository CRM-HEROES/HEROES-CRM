<?php

namespace App\Jobs;

use App\Exports\ProspectDownload;
use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PrepareProspectDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;
    public bool $failOnTimeout = true;

    public function __construct(public Export $export)
    {
    }

    public function handle(ProspectDownload $download): void
    {
        $previousUser = Auth::user();
        $previousInput = request()->all();
        $previousTeam = getPermissionsTeamId();

        try {
            $download->setStatus($this->export, 'processing');
            $creator = $this->export->creator;

            if (!$creator || !$this->export->project) {
                throw new RuntimeException('Le créateur ou le projet de l’export est introuvable.');
            }

            // Preserve the same prospect visibility rules as the HTTP request.
            Auth::setUser($creator);
            setPermissionsTeamId($this->export->project_id);
            request()->merge(['project' => $this->export->project]);

            Log::info('Prospect download: preparing', ['export_id' => $this->export->id]);
            $download->prepare($this->export);
            Log::info('Prospect download: ready', ['export_id' => $this->export->id]);
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
        app(ProspectDownload::class)->setStatus($this->export, 'failed');
        Log::error('Prospect download: failed', [
            'export_id' => $this->export->id,
            'exception' => $exception,
        ]);
    }
}
