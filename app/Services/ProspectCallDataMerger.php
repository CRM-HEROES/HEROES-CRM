<?php

namespace App\Services;

use App\Models\Prospect;

/**
 * Shared "safe auto-fill" rule for prospect data extracted from a call
 * (post-call transcript analysis or a live AI phone agent): only known,
 * non-empty fields are applied, and an existing value on the prospect is
 * never overwritten.
 */
class ProspectCallDataMerger
{
    public const ALLOWED_FIELDS = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'mobile_phone_number',
        'company_name',
        'job_title',
        'website_url',
        'street',
        'postal_code',
        'city',
        'country',
    ];

    /**
     * @param array $analysis Shape: { summary, qualification, needs, objections,
     *   next_steps, extracted: { first_name, ..., budget, project } }
     */
    public function buildProspectUpdates(Prospect $prospect, array $analysis): array
    {
        $updates = [];

        foreach (self::ALLOWED_FIELDS as $field) {
            $value = data_get($analysis, 'extracted.'.$field);
            if (is_string($value) && trim($value) !== '' && blank($prospect->{$field})) {
                $updates[$field] = trim($value);
            }
        }

        return $updates;
    }

    public function buildMeta(Prospect $prospect, array $analysis, string $metaKey): array
    {
        $meta = $prospect->meta ?: [];

        $meta[$metaKey] = [
            'summary' => data_get($analysis, 'summary'),
            'qualification' => data_get($analysis, 'qualification'),
            'needs' => data_get($analysis, 'needs', []),
            'objections' => data_get($analysis, 'objections', []),
            'next_steps' => data_get($analysis, 'next_steps', []),
            'budget' => data_get($analysis, 'extracted.budget'),
            'project' => data_get($analysis, 'extracted.project'),
        ];

        return $meta;
    }
}
