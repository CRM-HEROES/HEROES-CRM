<?php

namespace App\Console\Commands;

use App\Exports\ProspectDownload;
use App\Exports\ProspectSheet;
use App\Jobs\ExportProspects;
use App\Models\Export;
use App\Models\GoogleAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class DispatchScheduledProspectExports extends Command
{
    protected $signature = 'exports:dispatch-scheduled-prospects';

    protected $description = 'Envoie les exports de prospects dont la date de planification est atteinte';

    public function handle(): int
    {
        $now = now();
        $staleClaim = $now->copy()->subMinutes(10);

        Export::query()
            ->whereNotNull('scheduled_at')
            ->whereNull('scheduled_dispatched_at')
            ->where('scheduled_at', '<=', $now)
            ->where(function ($query) use ($staleClaim) {
                $query->whereNull('scheduled_claimed_at')
                    ->orWhere('scheduled_claimed_at', '<=', $staleClaim);
            })
            ->orderBy('id')
            ->chunkById(100, function ($exports) use ($staleClaim) {
                foreach ($exports as $export) {
                    $claimedAt = now();
                    $claimed = Export::query()
                        ->whereKey($export->id)
                        ->whereNull('scheduled_dispatched_at')
                        ->where(function ($query) use ($staleClaim) {
                            $query->whereNull('scheduled_claimed_at')
                                ->orWhere('scheduled_claimed_at', '<=', $staleClaim);
                        })
                        ->update(['scheduled_claimed_at' => $claimedAt]);

                    if (!$claimed) {
                        continue;
                    }

                    try {
                        $this->dispatchExport($export);

                        Export::query()->whereKey($export->id)->update([
                            'scheduled_claimed_at' => null,
                            'scheduled_dispatched_at' => now(),
                        ]);
                    } catch (Throwable $exception) {
                        // Release the claim so a transient queue or storage error can retry next minute.
                        Export::query()->whereKey($export->id)->update(['scheduled_claimed_at' => null]);
                        report($exception);
                        $this->error("L’export planifié #{$export->id} n’a pas pu être envoyé : {$exception->getMessage()}");
                    }
                }
            });

        return self::SUCCESS;
    }

    protected function dispatchExport(Export $export): void
    {
        if ($export->mode === 'email') {
            ExportProspects::dispatch($export)->onQueue('imports');
            return;
        }

        if ($export->mode !== 'sheet') {
            throw new \RuntimeException('Le mode de cet export planifié n’est pas pris en charge.');
        }

        $account = GoogleAccount::query()
            ->where('project_id', $export->project_id)
            ->where('for', 'drive')
            ->first();

        if (!$account) {
            app(ProspectDownload::class)->setStatus($export, 'failed', [
                'mode' => 'sheet',
                'message' => ProspectSheet::CONNECTION_MESSAGE,
            ]);
            return;
        }

        $title = trim((string) $export->sheet_name);
        if ($title === '') {
            $title = Str::limit($export->project->name . ' - Prospects', 150, '');
        }

        app(ProspectSheet::class)->enqueue($export, $account, $title);
    }
}
