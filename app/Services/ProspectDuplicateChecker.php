<?php

namespace App\Services;

use App\Models\Prospect;
use Illuminate\Support\Collection;

/**
 * Real-time counterpart to App\Jobs\CheckDuplicatedProspects: instead of
 * rescanning the whole project (fine for the manual "Trouver les doublons"
 * action, far too expensive to run on every single save), this checks one
 * just-created/updated prospect against the rest of the project, and only
 * for fields the admin flagged "unique" via the field header's duplicate
 * toggle (see DefaultHeaderCell.vue's "Vérifier doublon lors de la saisie").
 *
 * Matching is OR across unique fields (any one matching field is enough),
 * unlike the manual panel's AND-across-selected-fields — each field's
 * "unique" flag is independent, so a match on any of them is a duplicate.
 *
 * duplicate_fields records exactly which field(s) matched, so the table
 * can color only the offending cell (e.g. Email) instead of the whole row.
 */
class ProspectDuplicateChecker
{
    /**
     * @return Collection<int, Prospect> Every other prospect touched by this
     * check (the "partners"), so the caller can push their fresh
     * duplicate_group_id/duplicate_fields into the frontend store — without
     * this, a partner already loaded in the table would only ever pick up
     * its own new color after a full page reload.
     */
    public function check(Prospect $prospect): Collection
    {
        $project = $prospect->project;
        if (!$project) {
            return collect();
        }

        $uniqueFields = $project->fields()
            ->where('for', 'prospect')
            ->where('unique', true)
            ->get();

        if ($uniqueFields->isEmpty()) {
            return collect();
        }

        $matchedFields = [];
        $groupId = null;
        $pointerId = null;
        $touchedPartners = collect();

        foreach ($uniqueFields as $field) {
            $match = $this->matchField($prospect, $field);
            if (!$match) {
                continue;
            }

            $matchedFields[] = $field->slug;
            $groupId = $groupId ?? $match['group_id'];
            $pointerId = $pointerId ?? $match['partner_ids']->first();

            $touchedPartners = $touchedPartners->merge(
                $this->tagPartners($match['partner_ids'], $field->slug, $match['group_id'])
            );
        }

        if (empty($matchedFields)) {
            if ($prospect->duplicate_group_id) {
                // Used to be part of a cluster but no longer matches
                // anyone on any currently-unique field (e.g. a duplicate
                // email just got corrected) — leave it; the rest of the
                // cluster is untouched.
                $prospect->duplicate_id = null;
                $prospect->duplicate_group_id = null;
                $prospect->duplicate_fields = null;
                $prospect->saveQuietly();
            }
            return $touchedPartners;
        }

        $prospect->duplicate_group_id = $groupId;
        $prospect->duplicate_fields = $matchedFields;
        if (!$prospect->duplicate_id) {
            $prospect->duplicate_id = $pointerId;
        }
        $prospect->saveQuietly();

        return $touchedPartners->reject(fn ($p) => $p->id === $prospect->id)->unique('id');
    }

    /**
     * @return array{group_id: int, partner_ids: Collection}|null
     */
    protected function matchField(Prospect $prospect, $field): ?array
    {
        $value = $field->meta
            ? data_get($prospect->meta, $field->slug)
            : $prospect->{$field->slug};

        if (blank($value)) {
            return null;
        }

        $query = Prospect::withoutGlobalScopes()
            ->where('project_id', $prospect->project_id)
            ->where('id', '!=', $prospect->id);

        if ($field->meta) {
            $query->whereRaw("JSON_EXTRACT(meta, '\$.$field->slug') = ?", [json_encode($value)]);
        } else {
            $query->where($field->slug, $value);
        }

        $matches = $query->get(['id', 'duplicate_group_id']);
        if ($matches->isEmpty()) {
            return null;
        }

        // Join whichever cluster a match already belongs to instead of
        // starting a new one, so this doesn't fragment an existing group.
        $groupId = $matches->pluck('duplicate_group_id')->filter()->min()
            ?? $matches->pluck('id')->push($prospect->id)->min();

        return [
            'group_id' => $groupId,
            'partner_ids' => $matches->pluck('id'),
        ];
    }

    /**
     * @return Collection<int, Prospect> the freshly-updated partner rows
     */
    protected function tagPartners(Collection $partnerIds, string $fieldSlug, int $groupId): Collection
    {
        $partners = Prospect::withoutGlobalScopes()->whereIn('id', $partnerIds)->get(['id', 'duplicate_fields']);

        foreach ($partners as $partner) {
            $fields = collect($partner->duplicate_fields ?: [])->push($fieldSlug)->unique()->values()->all();

            Prospect::withoutGlobalScopes()->whereKey($partner->id)->update([
                'duplicate_group_id' => $groupId,
                'duplicate_fields' => json_encode($fields),
            ]);

            $partner->duplicate_group_id = $groupId;
            $partner->duplicate_fields = $fields;
        }

        return $partners;
    }
}
