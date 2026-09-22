<?php

namespace App\Support;

use Illuminate\Support\Collection;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * Detects which country a phone number belongs to, to route imported leads
 * to agents configured for that country (ProspectAutoAssignment). Only a
 * number written with an explicit country code (+33..., 0033..., etc.) can
 * be classified worldwide — libphonenumber has no way to guess a country
 * from a bare local number without already assuming one, and most countries'
 * local formats overlap with at least one other country's. A small,
 * hand-verified fallback covers Belgium/France local numbers only, since
 * this CRM's leads were historically BE/FR before going international and
 * those two happen not to overlap (barring one genuinely ambiguous prefix).
 */
class PhoneCountry
{
    /**
     * Returns the dialing code (e.g. "+33", "+32") a phone number belongs
     * to, or null if it can't be determined. This is the format
     * User::phone_country is stored in (see PhoneCountrySelect.vue), unlike
     * detect() which returns an ISO region code — the two are not
     * interchangeable despite both describing "a country".
     */
    public static function detectDialCode(?string $number): ?string
    {
        return self::dialCodeForRegion(self::detect($number));
    }

    /**
     * Converts an ISO 3166-1 alpha-2 region code (as returned by detect())
     * into its dialing code, e.g. "FR" -> "+33".
     */
    public static function dialCodeForRegion(?string $regionCode): ?string
    {
        if (!$regionCode) {
            return null;
        }

        $callingCode = PhoneNumberUtil::getInstance()->getCountryCodeForRegion($regionCode);

        return $callingCode ? '+' . $callingCode : null;
    }

    /**
     * Narrows $users (anything iterable of objects exposing ->phone_country,
     * a JSON array of dial codes such as ["+33", "+32"]) to those configured
     * for $dialCode, or with no country preference at all. Every caller that
     * routes a lead to a set of users by phone indicatif — ProspectAutoAssignment,
     * ImportProspects's "Utilisateurs affectés" step, the coregistration
     * webservice — must go through this single place: it's already been
     * reimplemented ad hoc once for a new prospect-creation path and quietly
     * skipped the country rule entirely, so a second copy is exactly the bug
     * to avoid.
     *
     * A user is never left without a candidate for lack of a match: if
     * $dialCode can't be determined, or no user matches it, the input is
     * returned unfiltered instead of coming back empty.
     */
    public static function filterUsersByDialCode($users, ?string $dialCode): Collection
    {
        $users = $users instanceof Collection ? $users : collect($users);

        if (!$dialCode) {
            return $users;
        }

        $matching = $users->filter(function ($user) use ($dialCode) {
            $configuredDialCodes = $user->phone_country;

            return empty($configuredDialCodes) || in_array($dialCode, $configuredDialCodes, true);
        })->values();

        return $matching->isEmpty() ? $users : $matching;
    }

    /**
     * Returns an ISO 3166-1 alpha-2 country code, or null if it can't be
     * determined.
     */
    public static function detect(?string $number): ?string
    {
        $value = trim((string) $number);
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[\s.\-()]+/', '', $value);

        if (!str_starts_with($normalized, '+') && !str_starts_with($normalized, '00')) {
            return self::detectFromBelgianOrFrenchLocalFormat($normalized);
        }

        $e164 = str_starts_with($normalized, '00')
            ? '+' . substr($normalized, 2)
            : $normalized;

        try {
            $util = PhoneNumberUtil::getInstance();
            $parsed = $util->parse($e164, null);

            return $util->getRegionCodeForNumber($parsed) ?: null;
        } catch (NumberParseException $exception) {
            return null;
        }
    }

    /**
     * France has no 9-digit numbers, and Belgium's only 10-digit prefix is
     * mobile "04" — everything else 10-digit is unambiguously French. The
     * exception is 10-digit numbers starting with "04" (Belgian mobile vs.
     * French Provence landline), which is genuinely undecidable without the
     * country code and is reported as unknown rather than guessed.
     */
    protected static function detectFromBelgianOrFrenchLocalFormat(string $value): ?string
    {
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
