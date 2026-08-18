<?php

namespace App\Services\Archer;

use App\Models\Prospect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Builds the nightly ARCHER targeting query: prospects matching the
 * configurable criteria (age, zone, sector, appetency). Console commands run
 * outside an authenticated request, so this deliberately bypasses
 * ProspectScope (see App\Jobs\GenerateAiQuoteDraft for the same pattern).
 *
 * @phpstan-type ArcherCriteria array{
 *   project_id?: int|null,
 *   min_age?: int|null,
 *   max_age?: int|null,
 *   zones?: string[]|null,
 *   sectors?: string[]|null,
 *   min_appetency?: int|null,
 * }
 */
class ArcherTargetingService
{
    public function query(array $criteria = []): Builder
    {
        $query = Prospect::withoutGlobalScopes();

        if (!empty($criteria['project_id'])) {
            $query->where('project_id', $criteria['project_id']);
        }

        $this->applyAgeRange($query, $criteria['min_age'] ?? null, $criteria['max_age'] ?? null);
        $this->applyZones($query, $criteria['zones'] ?? []);
        $this->applySectors($query, $criteria['sectors'] ?? []);

        if (!empty($criteria['min_appetency'])) {
            $query->where('appetency_score', '>=', (int) $criteria['min_appetency']);
        }

        return $query;
    }

    protected function applyAgeRange(Builder $query, ?int $minAge, ?int $maxAge): void
    {
        if ($minAge === null && $maxAge === null) {
            return;
        }

        $query->whereNotNull('date_of_birth');

        // A minimum age means the birth date is further in the past
        // (an older upper bound on the date), and vice versa.
        if ($minAge !== null) {
            $query->where('date_of_birth', '<=', Carbon::now()->subYears($minAge));
        }

        if ($maxAge !== null) {
            $query->where('date_of_birth', '>=', Carbon::now()->subYears($maxAge + 1));
        }
    }

    protected function applyZones(Builder $query, array $zones): void
    {
        $zones = array_filter($zones);
        if (empty($zones)) {
            return;
        }

        $query->where(function (Builder $zoneQuery) use ($zones) {
            foreach ($zones as $zone) {
                $zoneQuery
                    ->orWhere('city', 'like', "%{$zone}%")
                    ->orWhere('postal_code', 'like', "{$zone}%")
                    ->orWhere('state', 'like', "%{$zone}%")
                    ->orWhere('county', 'like', "%{$zone}%");
            }
        });
    }

    protected function applySectors(Builder $query, array $sectors): void
    {
        $sectors = array_filter($sectors);
        if (empty($sectors)) {
            return;
        }

        $query->whereIn('sector', $sectors);
    }
}
