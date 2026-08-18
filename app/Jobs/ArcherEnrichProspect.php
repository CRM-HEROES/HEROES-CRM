<?php

namespace App\Jobs;

use App\Models\Prospect;
use App\Models\ProspectEnrichment;
use App\Services\Archer\ArcherScorer;
use App\Services\Archer\DropcontactClient;
use App\Services\Archer\ProxycurlClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * One ARCHER enrichment pass for a single prospect: verify contact info via
 * Dropcontact, resolve a LinkedIn profile via Proxycurl, compute the ARCHER
 * score, and write both the audit row (prospect_enrichments) and the
 * denormalized fields on the prospect used for sorting/filtering.
 *
 * Ranking into the top 20% ("tête de file") happens afterwards, once the
 * whole nightly batch has scored — see App\Jobs\ArcherRankProspects and
 * App\Console\Commands\ArcherEnrichProspects.
 */
class ArcherEnrichProspect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public int $prospectId) {}

    public function handle(DropcontactClient $dropcontact, ProxycurlClient $proxycurl, ArcherScorer $scorer): void
    {
        $prospect = Prospect::withoutGlobalScopes()->find($this->prospectId);
        if (!$prospect) {
            Log::channel('archer')->warning('ARCHER enrichment skipped: prospect not found.', [
                'prospect_id' => $this->prospectId,
            ]);
            return;
        }

        try {
            $emailResult = $dropcontact->verify($prospect);
            $linkedinResult = $proxycurl->lookup($prospect);
        } catch (\Throwable $exception) {
            $this->recordFailure($prospect, $exception);
            throw $exception;
        }

        $enrichment = [
            'email_verified' => $emailResult['email_verified'] ?? null,
            'phone_verified' => $linkedinResult['phone_verified'] ?? null,
            'linkedin_url' => $linkedinResult['linkedin_url'] ?? null,
        ];

        $score = $scorer->score($enrichment, $prospect->appetency_score);
        $now = now();

        ProspectEnrichment::create([
            'prospect_id' => $prospect->id,
            'status' => 'success',
            'email_verified' => $enrichment['email_verified'],
            'email_verified_at' => $enrichment['email_verified'] !== null ? $now : null,
            'phone_verified' => $enrichment['phone_verified'],
            'phone_verified_at' => $enrichment['phone_verified'] !== null ? $now : null,
            'linkedin_url' => $enrichment['linkedin_url'],
            'dropcontact_data' => $emailResult['raw'] ?? null,
            'proxycurl_data' => $linkedinResult['raw'] ?? null,
            'score' => $score,
        ]);

        // Verified data only overwrites contact fields when the prospect
        // didn't already have one, so a bad lookup can never clobber good
        // pre-existing data — it only ever fills gaps.
        $prospect->fill([
            'email' => $prospect->email ?: ($emailResult['email'] ?? null),
            'verified_email' => (bool) ($enrichment['email_verified'] ?? $prospect->verified_email),
            'verified_phone' => (bool) ($enrichment['phone_verified'] ?? $prospect->verified_phone),
            'linkedin_url' => $prospect->linkedin_url ?: $enrichment['linkedin_url'],
            'archer_score' => $score,
            'archer_scored_at' => $now,
        ]);
        $prospect->save();

        Log::channel('archer')->info('ARCHER enrichment complete.', [
            'prospect_id' => $prospect->id,
            'score' => $score,
        ]);
    }

    protected function recordFailure(Prospect $prospect, \Throwable $exception): void
    {
        ProspectEnrichment::create([
            'prospect_id' => $prospect->id,
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ]);

        Log::channel('archer')->error('ARCHER enrichment failed.', [
            'prospect_id' => $prospect->id,
            'message' => $exception->getMessage(),
        ]);
    }
}
