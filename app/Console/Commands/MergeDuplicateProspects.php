<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * One-time cleanup for prospects already duplicated (same email) before
 * App\Http\Controllers\API\Project\ProspectController::store()'s
 * find-or-update fix existed. Unlike the existing manual "Rechercher des
 * duplicatas" UI (App\Jobs\HandleDuplicatedProspects), which only acts on
 * prospects the real-time/bulk checker already flagged via
 * duplicate_group_id, this recomputes clusters directly from the email
 * column and can run in bulk across every project in one pass.
 *
 * For each cluster of prospects sharing the same project + normalized
 * email:
 *   - the "survivor" is the one with the most related records (events,
 *     orders, messages, interactions, sms, files, documents, links —
 *     picking blindly by "oldest" could delete the very row that has the
 *     real appointment/order attached), tie-broken by having a non-blank
 *     name, then by being the oldest;
 *   - every other prospect in the cluster ("losers") has its related rows
 *     reassigned to the survivor (not dropped), then gets soft-deleted.
 *
 * Dry-run by default: reports what would happen without changing
 * anything. Pass --commit to actually apply it. Every commit run appends
 * a JSON audit log to storage/logs/merge-duplicate-prospects.log (which
 * prospects were merged into which survivor) since this can't be undone.
 */
class MergeDuplicateProspects extends Command
{
    protected $signature = 'app:merge-duplicate-prospects {--project=} {--commit}';

    protected $description = 'Merge prospects duplicated by email within each project (dry-run unless --commit is passed)';

    /**
     * Tables with a plain (non-unique) prospect_id foreign key: every
     * related row can be reassigned to the survivor with no conflict.
     */
    const PLAIN_TABLES = [
        'messages', 'files', 'interactions', 'orders', 'events',
        'available_events', 'sms', 'prospect_questionnaire_responses',
        'prospect_documents', 'links', 'campaign_prospect', 'ocrs',
        'prospect_label', 'google_folder',
    ];

    /**
     * Tables with a UNIQUE(prospect_id, partner column) constraint: a
     * loser's row can only move to the survivor if the survivor doesn't
     * already have a row for that same partner value, otherwise it's
     * simply dropped (the survivor's own row already covers it).
     */
    const UNIQUE_TABLES = [
        'prospect_user' => 'user_id',
        'prospect_wait_for_user' => 'user_id',
        'prospect_group' => 'group_id',
        'prospect_questionnaire' => 'questionnaire_id',
        'google_contact' => 'google_account_id',
        'pipedrive_person' => 'pipedrive_account_id',
    ];

    /**
     * Tables counted towards a prospect's "activity score" when picking
     * the survivor of a cluster.
     */
    const ACTIVITY_TABLES = [
        'events', 'orders', 'messages', 'interactions', 'sms', 'files',
        'prospect_documents', 'links',
    ];

    public function handle(): int
    {
        $commit = $this->option('commit');

        if (!$commit) {
            $this->warn('DRY RUN (no changes will be made) — pass --commit to actually apply.');
        }

        $projects = Project::when($this->option('project'), function ($query) {
            $query->where('id', $this->option('project'));
        })->get(['id', 'name']);

        $totalClusters = 0;
        $totalMerged = 0;

        foreach ($projects as $project) {
            $clusters = $this->getClusters($project);

            if ($clusters->isEmpty()) {
                continue;
            }

            $this->info("\n{$project->name} (id={$project->id}): " . $clusters->count() . ' cluster(s) de doublons');

            foreach ($clusters as $email => $ids) {
                $survivor = $this->pickSurvivor($ids);
                $losers = array_values(array_diff($ids, [$survivor]));

                $this->line("  - $email: garde #$survivor, fusionne " . implode(', ', array_map(fn ($id) => "#$id", $losers)));

                if ($commit) {
                    DB::transaction(function () use ($survivor, $losers) {
                        $this->mergeInto($survivor, $losers);
                    });

                    Log::channel('single')->info('merge-duplicate-prospects', [
                        'survivor' => $survivor,
                        'merged' => $losers,
                    ]);
                }

                ++$totalClusters;
                $totalMerged += count($losers);
            }
        }

        $this->info("\nTotal: $totalClusters cluster(s), $totalMerged prospect(s) " . ($commit ? 'fusionné(s) et supprimé(s).' : 'seraient fusionné(s) (dry-run).'));

        return self::SUCCESS;
    }

    /**
     * Group this project's non-deleted, non-blank-email prospects by
     * normalized email, keeping only clusters of 2+.
     *
     * @return \Illuminate\Support\Collection<string, array<int>>
     */
    protected function getClusters(Project $project)
    {
        return DB::table('prospects')
            ->where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->select('id', DB::raw('LOWER(TRIM(email)) as normalized_email'))
            ->orderBy('id')
            ->get()
            ->groupBy('normalized_email')
            ->filter(fn ($rows) => $rows->count() > 1)
            ->map(fn ($rows) => $rows->pluck('id')->all());
    }

    /**
     * @param  array<int>  $ids
     */
    protected function pickSurvivor(array $ids): int
    {
        $rows = DB::table('prospects')
            ->whereIn('id', $ids)
            ->get(['id', 'first_name', 'last_name', 'created_at']);

        $scored = $rows->map(function ($row) {
            return [
                'id' => $row->id,
                'activity' => $this->activityScore($row->id),
                'has_name' => trim(($row->first_name ?? '') . ($row->last_name ?? '')) !== '',
                'created_at' => $row->created_at,
            ];
        });

        $best = $scored->sort(function ($a, $b) {
            if ($a['activity'] !== $b['activity']) {
                return $b['activity'] <=> $a['activity']; // most related records first
            }
            if ($a['has_name'] !== $b['has_name']) {
                return ($b['has_name'] ? 1 : 0) <=> ($a['has_name'] ? 1 : 0); // non-blank name first
            }
            return $a['created_at'] <=> $b['created_at']; // oldest first
        })->first();

        return $best['id'];
    }

    protected function activityScore(int $prospectId): int
    {
        $score = 0;

        foreach (self::ACTIVITY_TABLES as $table) {
            $score += DB::table($table)->where('prospect_id', $prospectId)->count();
        }

        return $score;
    }

    /**
     * Reassign every loser's related rows to the survivor (skipping ones
     * that would violate a unique constraint, since the survivor already
     * has that relation), then soft-delete the losers.
     *
     * @param  array<int>  $losers
     */
    protected function mergeInto(int $survivor, array $losers): void
    {
        foreach ($losers as $loser) {
            foreach (self::PLAIN_TABLES as $table) {
                DB::table($table)->where('prospect_id', $loser)->update(['prospect_id' => $survivor]);
            }

            foreach (self::UNIQUE_TABLES as $table => $partnerColumn) {
                $survivorPartners = DB::table($table)
                    ->where('prospect_id', $survivor)
                    ->pluck($partnerColumn)
                    ->all();

                DB::table($table)
                    ->where('prospect_id', $loser)
                    ->when(!empty($survivorPartners), function ($query) use ($partnerColumn, $survivorPartners) {
                        $query->whereNotIn($partnerColumn, $survivorPartners);
                    })
                    ->update(['prospect_id' => $survivor]);

                // Any row left on the loser now conflicts with one the
                // survivor already has — the relation itself is already
                // preserved via the survivor's row, so it's safe to drop.
                DB::table($table)->where('prospect_id', $loser)->delete();
            }
        }

        DB::table('prospects')->whereIn('id', $losers)->update(['deleted_at' => now()]);
    }
}
