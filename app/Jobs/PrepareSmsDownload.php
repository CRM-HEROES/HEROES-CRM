<?php

namespace App\Jobs;

use App\Exports\SmsDownload;
use App\Models\Project;
use App\Models\Scopes\ProjectScope;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PrepareSmsDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;
    public bool $failOnTimeout = true;

    public function __construct(public string $exportId)
    {
    }

    public function handle(SmsDownload $download): void
    {
        $previousUser = Auth::user();
        $previousInput = request()->all();
        $previousTeam = getPermissionsTeamId();

        try {
            $metadata = $download->status($this->exportId);
            if ($metadata['status'] === 'missing' || $metadata['status'] === 'ready') {
                return;
            }
            $creator = User::find($metadata['creator_id']);
            // A reused worker can still hold the previous job's authenticated user.
            $project = Project::withoutGlobalScope(ProjectScope::class)->find($metadata['project_id']);
            if (!$creator || !$project) {
                throw new RuntimeException('Le créateur ou le projet de l’export SMS est introuvable.');
            }

            Auth::setUser($creator);
            setPermissionsTeamId($project->id);
            request()->merge(['project' => $project]);
            if (!$creator->can('', $project)) {
                throw new RuntimeException('Le créateur ne peut plus exporter les SMS du projet.');
            }

            $download->prepare($this->exportId);
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
        $download = app(SmsDownload::class);
        if ($download->status($this->exportId)['status'] !== 'missing') {
            $download->setStatus($this->exportId, 'failed');
        }
        Log::error('SMS download: failed', [
            'export_id' => $this->exportId,
            'exception_type' => $exception ? get_class($exception) : null,
        ]);
    }
}
