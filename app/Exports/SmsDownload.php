<?php

namespace App\Exports;

use App\Jobs\DeleteSmsDownload;
use App\Jobs\PrepareSmsDownload;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SmsDownload
{
    public function enqueue(Project $project, User $creator, array $data): string
    {
        $id = (string) Str::uuid();
        $expiresAt = now()->addDay();
        $this->setStatus($id, 'queued', [
            'project_id' => $project->id,
            'creator_id' => $creator->id,
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'timezone' => $data['timezone'],
            'count' => (int) ($data['count'] ?? 0),
            'created_at' => now()->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        try {
            PrepareSmsDownload::dispatch($id)->onQueue('imports');
        } catch (Throwable $exception) {
            $this->setStatus($id, 'failed');
            throw $exception;
        }

        // A synchronous queue ignores delays and would remove the new export immediately.
        $connection = config('queue.default');
        if (!in_array(config("queue.connections.{$connection}.driver"), ['sync', 'null', null], true)) {
            DeleteSmsDownload::dispatch($id)->onQueue('imports')->delay($expiresAt);
        }

        return $id;
    }

    public function status(string $id): array
    {
        $status = $this->readStatus($id);

        if (isset($status['expires_at']) && CarbonImmutable::parse($status['expires_at'])->isPast()) {
            $this->delete($id);
            return ['status' => 'missing'];
        }

        return $status;
    }

    public function setStatus(string $id, string $status, array $details = []): void
    {
        $this->assertValidId($id);
        $disk = Storage::disk('exports');
        $temporaryPath = $this->directory($id) . '/status-' . Str::uuid() . '.tmp';
        $current = $this->readStatus($id);

        try {
            // Keep the ownership and filter metadata while publishing a complete status atomically.
            $payload = array_merge($current, $details, [
                'status' => $status,
                'updated_at' => now()->toIso8601String(),
            ]);
            if (!$disk->put($temporaryPath, json_encode($payload, JSON_THROW_ON_ERROR))
                || !$disk->move($temporaryPath, $this->statusPath($id))) {
                throw new RuntimeException('Impossible d’enregistrer l’état de l’export SMS.');
            }
        } finally {
            $disk->delete($temporaryPath);
        }
    }

    public function prepare(string $id): void
    {
        $metadata = $this->status($id);
        if ($metadata['status'] === 'missing') {
            throw new RuntimeException('L’export SMS est introuvable ou a expiré.');
        }
        if ($metadata['status'] === 'ready') {
            return;
        }

        $project = Project::find($metadata['project_id']);
        if (!$project) {
            throw new RuntimeException('Le projet de l’export SMS est introuvable.');
        }

        $disk = Storage::disk('exports');
        $disk->makeDirectory($this->directory($id));
        $path = $disk->path($this->filePath($id));
        $temporaryPath = tempnam(dirname($path), 'pending-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Impossible de créer le fichier temporaire d’export SMS.');
        }

        $output = null;
        try {
            $this->setStatus($id, 'processing');
            $output = fopen($temporaryPath, 'wb');
            if ($output === false) {
                throw new RuntimeException('Impossible d’ouvrir le fichier temporaire d’export SMS.');
            }

            $from = CarbonImmutable::parse($metadata['date_from'], $metadata['timezone'])->startOfDay()->utc();
            $until = CarbonImmutable::parse($metadata['date_to'], $metadata['timezone'])->addDay()->startOfDay()->utc();
            $rows = app(SmsCsvExport::class)->write($output, $project, $from, $until, (int) $metadata['count']);
            if (!fflush($output)) {
                throw new RuntimeException('Impossible de finaliser l’écriture du fichier CSV des SMS.');
            }
            fclose($output);
            $output = null;

            if (!rename($temporaryPath, $path)) {
                throw new RuntimeException('Impossible de finaliser le fichier d’export SMS.');
            }

            clearstatcache(true, $path);
            $this->setStatus($id, 'ready', ['rows' => $rows]);
        } catch (Throwable $exception) {
            $disk->delete($this->filePath($id));
            try {
                $this->setStatus($id, 'failed');
            } catch (Throwable $statusException) {
                // Preserve the original failure if storage cannot publish the failure state either.
            }
            throw $exception;
        } finally {
            if (is_resource($output)) {
                fclose($output);
            }
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    public function preparedFile(string $id): BinaryFileResponse
    {
        $metadata = $this->status($id);
        $disk = Storage::disk('exports');
        abort_unless($metadata['status'] === 'ready' && $disk->exists($this->filePath($id)), 409,
            'Le fichier d’export SMS n’est pas encore disponible.');

        return response()->download($disk->path($this->filePath($id)),
            'sms-' . $metadata['date_from'] . '-' . $metadata['date_to'] . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    public function delete(string $id): void
    {
        $this->assertValidId($id);
        Storage::disk('exports')->deleteDirectory($this->directory($id));
    }

    protected function readStatus(string $id): array
    {
        if (!Str::isUuid($id)) {
            return ['status' => 'missing'];
        }
        $disk = Storage::disk('exports');
        if (!$disk->exists($this->statusPath($id))) {
            return ['status' => 'missing'];
        }

        return json_decode($disk->get($this->statusPath($id)), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function assertValidId(string $id): void
    {
        if (!Str::isUuid($id)) {
            throw new RuntimeException('Identifiant d’export SMS invalide.');
        }
    }

    protected function directory(string $id): string
    {
        return 'sms-downloads/' . $id;
    }

    protected function statusPath(string $id): string
    {
        return $this->directory($id) . '/status.json';
    }

    protected function filePath(string $id): string
    {
        return $this->directory($id) . '/export.csv';
    }
}
