<?php

namespace App\Console\Commands;

use App\Jobs\ArcherEnrichProspect;
use App\Services\Archer\ArcherTargetingService;
use Illuminate\Console\Command;

/**
 * P6 — ARCHER nightly enrichment, targeting step. Selects prospects matching
 * the configurable criteria (age, zone, sector, appetency) and queues one
 * ArcherEnrichProspect job per prospect. Run "archer:rank" afterwards (see
 * its own scheduled slot in App\Console\Kernel) to flag the top 20%.
 */
class ArcherEnrichProspects extends Command
{
    protected $signature = 'archer:enrich
        {--project= : Limit to a single project ID}
        {--limit= : Max number of prospects to target this run}
        {--min-age= : Minimum age in years}
        {--max-age= : Maximum age in years}
        {--zone=* : City / postal code prefix / state to target (repeatable)}
        {--sector=* : Sector to target (repeatable)}
        {--min-appetency= : Minimum appetency score (0-100)}';

    protected $description = 'ARCHER (P6): target and queue prospects for nightly enrichment (Dropcontact + Proxycurl) and scoring.';

    public function handle(ArcherTargetingService $targeting)
    {
        $criteria = [
            'project_id' => $this->option('project'),
            'min_age' => $this->option('min-age') ?? config('archer.min_age'),
            'max_age' => $this->option('max-age') ?? config('archer.max_age'),
            'zones' => $this->option('zone') ?: $this->configList('archer.zones'),
            'sectors' => $this->option('sector') ?: $this->configList('archer.sectors'),
            'min_appetency' => $this->option('min-appetency') ?? config('archer.min_appetency'),
        ];

        $limit = (int) ($this->option('limit') ?: config('archer.batch_size'));

        $prospects = $targeting->query($criteria)
            ->orderBy('archer_scored_at')
            ->limit($limit)
            ->get(['id']);

        foreach ($prospects as $prospect) {
            ArcherEnrichProspect::dispatch($prospect->id);
        }

        $this->info("ARCHER: {$prospects->count()} prospect(s) mis en file d'enrichissement.");

        return self::SUCCESS;
    }

    protected function configList(string $key): array
    {
        $value = config($key);

        return $value ? array_filter(array_map('trim', explode(',', $value))) : [];
    }
}
