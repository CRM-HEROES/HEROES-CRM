<?php

namespace App\Exports;

use App\Jobs\PrepareProspectDownload;
use App\Models\Export;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProspectDownload
{
    public function enqueue(Export $export): void
    {
        $this->setStatus($export, 'queued');

        try {
            PrepareProspectDownload::dispatch($export)->onQueue('imports');
        } catch (Throwable $exception) {
            $this->setStatus($export, 'failed');
            throw $exception;
        }
    }

    public function status(Export $export): array
    {
        $disk = Storage::disk('exports');

        if (!$disk->exists($this->statusPath($export))) {
            return ['status' => 'missing'];
        }

        return json_decode($disk->get($this->statusPath($export)), true, 512, JSON_THROW_ON_ERROR);
    }

    public function setStatus(Export $export, string $status, array $details = []): void
    {
        $disk = Storage::disk('exports');
        $temporaryPath = $this->statusPath($export) . '.' . Str::uuid() . '.tmp';

        try {
            // Publish complete JSON so polling never reads a partially written status.
            if (!$disk->put($temporaryPath, json_encode(array_merge($details, [
                'status' => $status,
                'updated_at' => now()->toIso8601String(),
            ]), JSON_THROW_ON_ERROR)) || !$disk->move($temporaryPath, $this->statusPath($export))) {
                throw new RuntimeException('Impossible d’enregistrer l’état de l’export.');
            }
        } finally {
            $disk->delete($temporaryPath);
        }
    }

    public function prepare(Export $export): void
    {
        $disk = Storage::disk('exports');
        $disk->makeDirectory('downloads');
        $path = $disk->path($this->filePath($export));
        $temporaryPath = tempnam(dirname($path), 'pending-');

        if ($temporaryPath === false) {
            throw new RuntimeException('Impossible de créer le fichier temporaire d’export.');
        }

        try {
            $rows = new ProspectExport($export, (int) $export->count);
            $this->write($rows, $temporaryPath);

            if (!rename($temporaryPath, $path)) {
                throw new RuntimeException('Impossible de finaliser le fichier d’export.');
            }

            $this->setStatus($export, 'ready', ['rows' => $rows->getCount()]);
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    public function preparedFile(Export $export): BinaryFileResponse
    {
        $disk = Storage::disk('exports');
        abort_unless($this->status($export)['status'] === 'ready' && $disk->exists($this->filePath($export)), 409,
            'Le fichier d’export n’est pas encore disponible.');

        return response()->download($disk->path($this->filePath($export)), $export->project->name . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function delete(Export $export): void
    {
        Storage::disk('exports')->delete([$this->filePath($export), $this->statusPath($export)]);
    }

    protected function filePath(Export $export): string
    {
        return 'downloads/' . $export->id . '.xlsx';
    }

    protected function statusPath(Export $export): string
    {
        return 'downloads/' . $export->id . '.json';
    }

    public function download(ProspectExport $export, string $filename): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'prospect-export-');

        if ($path === false) {
            throw new RuntimeException('Impossible de créer le fichier temporaire d’export.');
        }

        try {
            $this->write($export, $path);

            // The file was empty when tempnam() created it.
            clearstatcache(true, $path);

            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (Throwable $exception) {
            if (is_file($path)) {
                unlink($path);
            }

            throw $exception;
        }
    }

    protected function write(ProspectExport $export, string $path): void
    {
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($path);

        try {
            foreach ($export->rows() as $row) {
                $values = array_map([$this, 'cellValue'], array_values($row));
                $writer->addRow(WriterEntityFactory::createRowFromArray($values));
            }
        } finally {
            $writer->close();
        }
    }

    protected function cellValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        return $value;
    }
}
