<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     * Key of the user_settings row holding, per user and project, the
     * countries whose leads the user accepts, for example:
     * [{"country_code":"BE","country_name":"Belgique","calling_code":"+32"}].
     */
    public const SETTING_KEY = 'assigned-phone-country-codes';

    /**
     * Dialing codes configured in user_settings for the given users within a
     * project: [userId => ["+32", "+33"]]. A user without any setting (or
     * with an empty one) is absent from the result, which means "no country
     * preference".
     */
    public static function dialCodesForUsers(array $userIds, $projectId): array
    {
        if (empty($userIds)) {
            return [];
        }

        $rows = DB::table('user_settings')
            ->whereIn('user_id', $userIds)
            ->where('project_id', $projectId)
            ->where('key', self::SETTING_KEY)
            ->pluck('value', 'user_id');

        $dialCodes = [];

        foreach ($rows as $userId => $value) {
            $countries = is_array($value) ? $value : json_decode((string) $value, true);
            if (!is_array($countries)) {
                continue;
            }

            $codes = [];
            foreach ($countries as $country) {
                $code = is_array($country) ? ($country['calling_code'] ?? null) : $country;
                if (is_string($code) && $code !== '') {
                    $codes[] = '+' . ltrim(trim($code), '+');
                }
            }

            if ($codes) {
                $dialCodes[(int) $userId] = array_values(array_unique($codes));
            }
        }

        return $dialCodes;
    }

    /**
     * Loads users with their ->phone_country attribute (array of dialing
     * codes) filled from user_settings, ready for filterUsersByDialCode().
     */
    public static function usersWithDialCodes(array $userIds, $projectId): Collection
    {
        $users = User::whereIn('id', $userIds)->get(['id']);
        $dialCodes = self::dialCodesForUsers($users->pluck('id')->all(), $projectId);

        return self::withDialCodes($users, $projectId, $dialCodes);
    }

    /**
     * Fills ->phone_country on already loaded users.
     */
    public static function withDialCodes($users, $projectId, ?array $dialCodes = null): Collection
    {
        $users = $users instanceof Collection ? $users : collect($users);
        $dialCodes = $dialCodes ?? self::dialCodesForUsers($users->pluck('id')->all(), $projectId);

        foreach ($users as $user) {
            $user->phone_country = $dialCodes[$user->id] ?? [];
        }

        return $users;
    }

    /**
     * Returns the dialing code (e.g. "+33", "+32") a phone number belongs
     * to, or null if it can't be determined. This is the format
     * User::phone_country is stored in (see PhoneCountrySelect.vue), unlike
     * detect() which returns an ISO region code — the two are not
     * interchangeable despite both describing "a country".
     */
    public static function detectDialCode(?string $number): ?string
    {
        $value = trim((string) $number);
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[\s.\-()]+/', '', $value);

        if (str_starts_with($normalized, '+') || str_starts_with($normalized, '00')) {
            $digits = preg_replace('/\D+/', '', $normalized);
            $digits = str_starts_with($normalized, '00') ? substr($digits, 2) : $digits;

            return self::dialCodeFromInternationalDigits($digits);
        }

        return self::dialCodeForRegion(self::detectFromBelgianOrFrenchLocalFormat($normalized));
    }

    /**
     * Country calling codes are prefix-free, so the first 1 to 3 leading
     * digits that form a known calling code identify the country.
     */
    protected static function dialCodeFromInternationalDigits(string $digits): ?string
    {
        $known = self::callingCodes();

        for ($length = 1; $length <= 3; $length++) {
            $prefix = substr($digits, 0, $length);

            if (strlen($prefix) === $length && isset($known[$prefix])) {
                return '+' . $prefix;
            }
        }

        return null;
    }

    /**
     * @return array<string, true> Known ITU country calling codes.
     */
    protected static function callingCodes(): array
    {
        static $codes = null;

        if ($codes === null) {
            $list = [
                1, 7,
                20, 27, 30, 31, 32, 33, 34, 36, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49,
                51, 52, 53, 54, 55, 56, 57, 58, 60, 61, 62, 63, 64, 65, 66,
                81, 82, 84, 86, 90, 91, 92, 93, 94, 95, 98,
                211, 212, 213, 216, 218,
                290, 291, 297, 298, 299,
                420, 421, 423,
                670, 672, 673, 674, 675, 676, 677, 678, 679, 680, 681, 682, 683, 685,
                686, 687, 688, 689, 690, 691, 692,
                850, 852, 853, 855, 856, 880, 886,
            ];

            foreach ([
                [220, 258], [260, 269], [350, 359], [370, 378], [380, 383], [385, 387],
                [500, 509], [590, 599], [960, 968], [970, 977], [992, 998],
            ] as [$from, $to]) {
                $list = array_merge($list, range($from, $to));
            }
            $list[] = 389;

            $codes = array_fill_keys(array_map('strval', $list), true);
        }

        return $codes;
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

        // Only the regions the local-format fallback can return, plus the
        // ones the CRM routes leads for.
        $callingCode = [
            'FR' => '33', 'BE' => '32', 'LU' => '352', 'CH' => '41', 'DE' => '49',
            'ES' => '34', 'IT' => '39', 'PT' => '351', 'NL' => '31', 'GB' => '44',
            'US' => '1', 'CA' => '1', 'MA' => '212', 'DZ' => '213', 'TN' => '216',
        ][strtoupper($regionCode)] ?? null;

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

        $dialCode = self::detectDialCode($normalized);

        $regions = [
            '+33' => 'FR', '+32' => 'BE', '+352' => 'LU', '+41' => 'CH', '+49' => 'DE',
            '+34' => 'ES', '+39' => 'IT', '+351' => 'PT', '+31' => 'NL', '+44' => 'GB',
            '+212' => 'MA', '+213' => 'DZ', '+216' => 'TN',
        ];

        return $regions[$dialCode] ?? null;
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
