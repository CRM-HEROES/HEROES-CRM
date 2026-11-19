<?php

namespace App\Console\Commands;

use App\Models\CallAttempt;
use Illuminate\Console\Command;

class CallsExpireStale extends Command
{
    protected $signature = 'calls:expire-stale';

    protected $description = 'Clôture les appels restés « en cours » dont le fournisseur n’a jamais renvoyé l’issue.';

    public function handle(): int
    {
        $limit = now()->subMinutes(max(1, (int) config('calls.stale_after_minutes', 60)));

        $count = CallAttempt::query()
            ->where('status', CallAttempt::CALLING)
            ->where('started_at', '<', $limit)
            ->update([
                'status' => CallAttempt::FAILED,
                'skip_reason' => 'Aucune issue reçue du fournisseur dans le délai imparti.',
                'ended_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("{$count} appel(s) clôturé(s).");

        return self::SUCCESS;
    }
}
