<?php

namespace App\Support;

/**
 * Phone number helpers shared by the telephony integrations.
 *
 * A PBX (Kavkom, an AI bridge...) presents a number in an international,
 * digit-only form (33688753390 or +33688753390) while the CRM stores what
 * the client typed (0688753390, "06 88 75 33 90"...). An incoming call
 * therefore has to be matched against every equivalent form of the number.
 */
class PhoneNumber
{
    /**
     * Fallback country code, used when the number itself does not tell
     * which country it belongs to (bare local number in an ambiguous
     * format, see PhoneCountry::detect()).
     */
    protected const DEFAULT_COUNTRY_DIAL_CODE = '+33';

    /**
     * Digits only, so any formatting (spaces, dashes, dots, +, ()) is
     * dropped: "+33 6 88 75 33 90" becomes "33688753390".
     */
    public static function digits(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number) ?: '';
    }

    /**
     * SQL expression returning the digits of a phone column, so a stored
     * number can be compared to a caller ID whatever its formatting.
     */
    public static function digitsExpression(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$column}, ' ', ''), '-', ''), '.', ''), '+', ''), '(', ''), ')', '')";
    }

    /**
     * Every digit form of a number that must be considered the same number.
     * A French number is stored locally (0688753390) but presented by the
     * PBX internationally (33688753390), so both are returned.
     *
     * @return array<int, string>
     */
    public static function candidates(?string $number): array
    {
        $digits = self::digits($number);

        if ($digits === '') {
            return [];
        }

        $candidates = [$digits];

        // International access prefix: 0033688753390 == 33688753390.
        if (str_starts_with($digits, '00')) {
            $candidates[] = substr($digits, 2);
        }

        $countryCode = self::countryCode($number);

        // foreach() iterates a copy of the array, so the variants appended
        // inside the loop are collected without being re-processed (which
        // would loop forever on "33..." <-> "0..." conversions).
        foreach ($candidates as $candidate) {
            if ($countryCode !== '' && str_starts_with($candidate, $countryCode)) {
                $candidates[] = '0' . substr($candidate, strlen($countryCode));
            } elseif (str_starts_with($candidate, '0')) {
                $candidates[] = ($countryCode ?: self::digits(self::DEFAULT_COUNTRY_DIAL_CODE))
                    . substr($candidate, 1);
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * Dialing code of the number's country, without the leading "+", or ''
     * when it cannot be determined.
     */
    protected static function countryCode(string $number): string
    {
        $dialCode = PhoneCountry::detectDialCode($number)
            ?: self::DEFAULT_COUNTRY_DIAL_CODE;

        return self::digits($dialCode);
    }
}
