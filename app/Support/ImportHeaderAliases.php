<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Single source of truth for recognising a spreadsheet/CSV column header as
 * a known CRM prospect field, shared by ImportProspects (applied when a row
 * is actually processed) and ImportObserver (applied once when an import's
 * mapping is first auto-generated). Both used to keep their own copy of
 * this list, which could silently drift apart when only one was updated.
 */
class ImportHeaderAliases
{
    /**
     * "Nom"/"Nom de famille" map straight to the CRM's last_name field —
     * not to full_name — so a source that only ever provides a family name
     * (no separate first name, no combined "Nom complet" column) is never
     * run through the first/last name splitter.
     */
    protected static array $aliases = [
        'email' => 'email',
        'e mail' => 'email',
        'mail' => 'email',
        'full name' => 'full_name',
        'nom complet' => 'full_name',
        'name' => 'full_name',
        'nom' => 'last_name',
        'nom de famille' => 'last_name',
        'last name' => 'last_name',
        'surname' => 'last_name',
        'phone number' => 'mobile_phone_number',
        'numero de telephone' => 'mobile_phone_number',
        'telephone' => 'mobile_phone_number',
        'telephone portable' => 'mobile_phone_number',
        'mobile' => 'mobile_phone_number',
        'phone' => 'mobile_phone_number',
        'created time' => 'created_at',
        'created at' => 'created_at',
        'date de creation' => 'created_at',
    ];

    /**
     * Normalize a header for comparison: strip accents/case/punctuation so
     * that "E-mail", "e_mail" and "E Mail" are recognised as the same header.
     */
    public static function normalize($header): string
    {
        $header = Str::ascii((string) $header);
        $header = str_replace(['\\_', '_'], ' ', $header);
        $header = preg_replace('/[^a-zA-Z0-9]+/', ' ', $header);

        return strtolower(trim(preg_replace('/\s+/', ' ', $header)));
    }

    /** Return the CRM field a known header maps to, or null if unrecognised. */
    public static function resolve($header): ?string
    {
        return self::$aliases[self::normalize($header)] ?? null;
    }
}
