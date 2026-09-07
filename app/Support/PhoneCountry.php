<?php

namespace App\Support;

/**
 * Detects whether a phone number is Belgian or French, to route imported
 * leads to agents configured for that country (ProspectAutoAssignment).
 * A bare local number (no country code) is only classifiable because
 * Belgium and France don't overlap on every prefix: France has no 9-digit
 * numbers, and Belgium's only 10-digit prefix is mobile "04" — everything
 * else 10-digit is unambiguously French. The exception is 10-digit numbers
 * starting with "04" (Belgian mobile vs. French Provence landline), which
 * is genuinely undecidable without the country code and is reported as
 * unknown rather than guessed.
 */
class PhoneCountry
{
    public const COUNTRIES = [
        'BE' => ['flag' => '🇧🇪', 'label' => 'Belgique', 'dial_code' => '32'],
        'FR' => ['flag' => '🇫🇷', 'label' => 'France', 'dial_code' => '33'],
    ];

    /**
     * Returns 'BE', 'FR', or null if the country can't be determined.
     */
    public static function detect(?string $number): ?string
    {
        $value = trim((string) $number);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[\s.\-()]+/', '', $value);

        if (str_starts_with($value, '+32') || str_starts_with($value, '0032')) {
            return 'BE';
        }

        if (str_starts_with($value, '+33') || str_starts_with($value, '0033')) {
            return 'FR';
        }

        if (str_starts_with($value, '+') || str_starts_with($value, '00')) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 9) {
            return 'BE';
        }

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $prefix = substr($digits, 0, 2);

            if ($prefix === '06' || $prefix === '07') {
                return 'FR';
            }

            if ($prefix === '04') {
                return null;
            }

            return 'FR';
        }

        return null;
    }
}
