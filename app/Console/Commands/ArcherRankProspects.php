<?php

namespace App\Console\Commands;

use App\Models\Prospect;
use App\Models\ProspectEnrichment;
use Illuminate\Console\Command;

/**
 * P6 — ARCHER nightly enrichment, ranking step. Runs after "archer:enrich"
 * has had time to work through its queue: flags the top N% (config
 * archer.top_percentile, default 20) of scored prospects per project as
 * archer_priority, so ProspectAutoAssignment::assignUnassignedProspects
 * sends them to the head of the assignment queue.
 *
 * Ranked per project rather than globally, so a single large project can't
 * fill the whole "top 20%" and starve smaller ones.
 */
class ArcherRankProspects extends Command
{
    protected $signature = 'archer:rank {--project= : Limit to a single project ID}';

    protected $description = 'ARCHER (P6): flag the top percentile of scored prospects as priority ("tête de file").';

    public function handle()
    {
        $percentile = (float) config('archer.top_percentile', 20);

        $projectsQuery = Prospect::withoutGlobalScopes()
            ->whereNotNull('archer_score')
            ->when($this->option('project'), fn ($q, $projectId) => $q->where('project_id', $projectId))
            ->select('project_id')
            ->distinct();

        $totalFlagged = 0;

        foreach ($projectsQuery->pluck('project_id') as $projectId) {
            $totalFlagged += $this->rankProject($projectId, $percentile);
        }

        $this->info("ARCHER: classement terminé, {$totalFlagged} prospect(s) en tête de file.");

        return self::SUCCESS;
    }

    protected function rankProject(?int $projectId, float $percentile): int
    {
        $scored = Prospect::withoutGlobalScopes()
            ->whereNotNull('archer_score')
            ->where('project_id', $projectId)
            ->orderByDesc('archer_score')
            ->orderBy('id')
            ->pluck('id');

        $total = $scored->count();
        if ($total === 0) {
            return 0;
        }

        $topCount = (int) max(1, ceil($total * $percentile / 100));
        $topIds = $scored->take($topCount)->all();
        $restIds = $scored->slice($topCount)->all();

        Prospect::withoutGlobalScopes()->whereIn('id', $topIds)->update(['archer_priority' => true]);
        if (!empty($restIds)) {
            Prospect::withoutGlobalScopes()->whereIn('id', $restIds)->update(['archer_priority' => false]);
        }

        $this->recordPercentiles($topIds, $restIds, $total);

        return count($topIds);
    }

    /**
     * Stamps each prospect's latest enrichment row with the percentile/
     * is_top_20 result of this ranking pass, for audit purposes only —
     * archer_priority on the prospect itself is what actually drives
     * assignment ordering.
     */
    protected function recordPercentiles(array $topIds, array $restIds, int $total): void
    {
        $latestIdsByProspect = ProspectEnrichment::whereIn('prospect_id', array_merge($topIds, $restIds))
            ->selectRaw('MAX(id) as id, prospect_id')
            ->groupBy('prospect_id')
            ->pluck('id', 'prospect_id');

        foreach ($topIds as $rank => $prospectId) {
            $enrichmentId = $latestIdsByProspect[$prospectId] ?? null;
            if (!$enrichmentId) {
                continue;
            }

            ProspectEnrichment::whereKey($enrichmentId)->update([
                'is_top_20' => true,
                'percentile' => round((($rank + 1) / $total) * 100, 2),
            ]);
        }

        if (empty($restIds)) {
            return;
        }

        $restEnrichmentIds = array_values(array_filter(array_map(
            fn ($id) => $latestIdsByProspect[$id] ?? null,
            $restIds
        )));

        if (!empty($restEnrichmentIds)) {
            ProspectEnrichment::whereIn('id', $restEnrichmentIds)->update(['is_top_20' => false]);
        }
    }
}
