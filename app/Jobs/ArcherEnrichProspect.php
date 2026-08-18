<?php

namespace App\Jobs;

use App\Models\Prospect;
use App\Models\ProspectEnrichment;
use App\Services\Archer\ArcherScorer;
use App\Services\Archer\DropcontactClient;
use App\Services\Archer\NinjaPearClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * One ARCHER enrichment pass for a single prospect: verify contact info via
 * Dropcontact, match a professional profile via NinjaPear, compute the
 * ARCHER score, and write both the audit row (prospect_enrichments) and the
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

    // Dropcontact's verification is async and gets polled inline (up to
    // ~60s, see DropcontactClient::$pollAttempts) — the default 60s queue
    // worker timeout would kill the job mid-poll, so this needs headroom.
    public int $timeout = 150;

    public function __construct(public int $prospectId) {}

    public function handle(DropcontactClient $dropcontact, NinjaPearClient $ninjaPear, ArcherScorer $scorer): void
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
            $profileResult = $ninjaPear->lookup($prospect);
        } catch (\Throwable $exception) {
            $this->recordFailure($prospect, $exception);
            throw $exception;
        }

        $enrichment = [
            'email_verified' => $emailResult['email_verified'] ?? null,
            // NinjaPear no longer verifies phone numbers (see NinjaPearClient
            // docblock) — the phone signal now comes from whether Dropcontact
            // itself returned a number for this contact.
            'phone_verified' => isset($emailResult['phone']) ? filled($emailResult['phone']) : null,
            'profile_found' => $profileResult['profile_found'] ?? null,
            'external_profile_url' => $profileResult['external_profile_url'] ?? null,
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
            'external_profile_url' => $enrichment['external_profile_url'],
            'dropcontact_data' => $emailResult['raw'] ?? null,
            'ninjapear_data' => $profileResult['raw'] ?? null,
            'score' => $score,
        ]);

        // Verified data only overwrites contact fields when the prospect
        // didn't already have one, so a bad lookup can never clobber good
        // pre-existing data — it only ever fills gaps.
        $prospect->fill([
            'email' => $prospect->email ?: ($emailResult['email'] ?? null),
            'phone_number' => $prospect->phone_number ?: ($emailResult['phone'] ?? null),
            'verified_email' => (bool) ($enrichment['email_verified'] ?? $prospect->verified_email),
            'verified_phone' => (bool) ($enrichment['phone_verified'] ?? $prospect->verified_phone),
            'external_profile_url' => $prospect->external_profile_url ?: $enrichment['external_profile_url'],
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
