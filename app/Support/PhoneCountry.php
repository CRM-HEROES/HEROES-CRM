<?php

namespace App\Support;

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
