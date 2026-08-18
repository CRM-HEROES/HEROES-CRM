<?php

namespace App\Services\Archer;

/**
 * Weighs an enrichment result into a single 0-100 ARCHER score. Deterministic
 * by design, same rationale as ProspectCallScorer: auditable and reproducible
 * for a nightly batch, instead of an LLM call per prospect.
 *
 * Weights: appetency is the strongest existing signal (up to 60 pts),
 * verified contact info is what makes a prospect actionable right now
 * (up to 30 pts), a resolved LinkedIn profile is a smaller confidence
 * boost (10 pts).
 */
class ArcherScorer
{
    public function score(array $enrichment, ?int $appetencyScore): int
    {
        $score = 0;

        $score += (int) round(min(100, max(0, $appetencyScore ?? 0)) * 0.6);

        if (!empty($enrichment['email_verified'])) {
            $score += 15;
        }

        if (!empty($enrichment['phone_verified'])) {
            $score += 15;
        }

        if (!empty($enrichment['linkedin_url'])) {
            $score += 10;
        }

        return max(0, min(100, $score));
    }
}
