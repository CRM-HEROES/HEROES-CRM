<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;
use Carbon\Carbon;

class DefaultField implements ImportColumnToFieldInterface
{
    protected $limitedDefaultFieldsSize = ['postal_code', 'phone_number', 'mobile_phone_number'];

    protected function formatDateValue($value)
    {
        $value = trim(str_replace("/", "-", $value));

        // Meta Lead Ads exports ISO-8601 timestamps with an offset, for example
        // 2026-07-30T02:10:07-05:00. Store the actual instant in UTC.
        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $value)) {
            try {
                return Carbon::parse($value)->utc()->format('Y-m-d H:i:s.v');
            } catch (\Exception $e) {
                // Keep the existing fallback for malformed values.
            }
        }

        if (
            preg_match(
                '/^(\\d{1,2})-(\\d{1,2})-(\\d{2,4})(.*)/', 
                $value, 
                $matches, 
                PREG_OFFSET_CAPTURE
            )
        ) {
            // The first number is the day, the second is the month, and the third is the year
            if ($matches[2][0] > 12) {
                $day = $matches[2][0];
                $matches[2][0] = $matches[1][0];
                $matches[1][0] = $day;
            }
            
            // If the day or month is a single digit, add a leading zero
            if (strlen($matches[1][0]) == 1) {
                $matches[1][0] = '0' . $matches[1][0];
            }
            
            if (strlen($matches[2][0]) == 1) {
                $matches[2][0] = '0' . $matches[2][0];
            }

            // If the year is two digits, add the century
            if (strlen($matches[3][0]) == 2) {
                $matches[3][0] = '20' . $matches[3][0];
            }

            $value = 
                $matches[3][0] . '-' . 
                $matches[2][0] . '-' . 
                $matches[1][0] . 
                $matches[4][0];
        }

        // If the time is not specified, add it
        if (strlen($value) == 10) {
            $value .= ' 00:00:00';
        } else if (strlen($value) == 13) {
            $value .= ':00:00';
        } else if (strlen($value) == 16) {
            $value .= ':00';
        }

        return $value;
    }

    /** Split a full name into the CRM's first_name and last_name columns. */
    protected function splitFullName($value): array
    {
        $value = trim(preg_replace('/\s+/', ' ', (string) $value));
        if ($value === '') {
            return [null, null];
        }

        // "LAST NAME, First name" is unambiguous.
        if (str_contains($value, ',')) {
            [$lastName, $firstName] = array_map('trim', explode(',', $value, 2));
            return [$firstName ?: null, $lastName ?: null];
        }

        $parts = explode(' ', $value);
        if (count($parts) === 1) {
            return [null, $parts[0]];
        }

        // Preserve all-uppercase family names, including prefixes such as
        // "DE LA CRUZ Juan" commonly found in exported lead lists.
        $uppercaseParts = 0;
        foreach ($parts as $part) {
            if (mb_strtoupper($part, 'UTF-8') === $part) {
                ++$uppercaseParts;
            } else {
                break;
            }
        }
        if ($uppercaseParts > 0 && $uppercaseParts < count($parts)) {
            return [
                implode(' ', array_slice($parts, $uppercaseParts)),
                implode(' ', array_slice($parts, 0, $uppercaseParts)),
            ];
        }

        return [implode(' ', array_slice($parts, 0, -1)), end($parts)];
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        if ($field == 'full_name') {
            [$prospect['first_name'], $prospect['last_name']] = $this->splitFullName($value);
            return;
        }

        $value = (string) $value;
        if (in_array($field, $this->limitedDefaultFieldsSize)) {
            $value = mb_substr($value, 0, 20);
        } else {
            $value = mb_substr($value, 0, 191);
        }

        if ($field == 'created_at') {
            if (empty($value)) {
                return;
            }

            $formatted = $this->formatDateValue($value);

            try {
                Carbon::parse($formatted);
            } catch (\Exception $e) {
                // Malformed value (e.g. a stray duplicated header row) — keep
                // the already computed fallback date instead of crashing the
                // insert with an invalid datetime.
                return;
            }

            $value = $formatted;
        }

        if ($field == 'mobile_phone_number' || $field == 'phone_number') {
            // Meta CSV exports prefix phones with "p:".
            $value = preg_replace('/^p\s*:\s*/i', '', $value);
        }

        $prospect[$field] = !empty($value) ? $value : null;
    }
}
