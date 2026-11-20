<?php

namespace App\Workflows\Actions;

use App\Models\Prospect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * What can keep an email from leaving, asked about a prospect: the options of the step "Envoyer
 * un email" that protect the person (no email to someone who unsubscribed, to a minor, in the days
 * around an appointment, twice in a day). See SendEmailAction.
 */
class EmailGuards
{
    public const OPTED_OUT = 'optout';
    public const MINOR = 'minor';
    public const QUIET_WINDOW = 'quiet_window';
    public const BUSY = 'busy';

    /**
     * The custom field the link "stop these emails" fills in (see the public page of the CRM)
     */
    public const OPTOUT_FIELD = 'mailing_optout';

    public const ADULT_AGE = 18;

    /**
     * Did the prospect ask not to receive these emails? (the field holds a date, "oui"... anything but empty, "non", "0")
     */
    public static function optedOut(Prospect $prospect): bool
    {
        $value = data_get($prospect->meta, self::OPTOUT_FIELD);

        return is_scalar($value) && !in_array(strtolower(trim((string) $value)), ['', 'non', 'no', '0', 'false', 'null'], true);
    }

    /**
     * Under 18, when the birth date is known
     */
    public static function minor(Prospect $prospect): bool
    {
        return $prospect->date_of_birth && Carbon::parse($prospect->date_of_birth)->age < self::ADULT_AGE;
    }

    /**
     * Is an appointment of the prospect within $before days ahead or $after days behind?
     */
    public static function quiet(Prospect $prospect, int $before, int $after): bool
    {
        if ($before <= 0 && $after <= 0) {
            return false;
        }

        return DB::table('events')
            ->where('prospect_id', $prospect->id)
            ->whereNull('deleted_at')
            ->whereBetween('started_at', [now()->subDays($after)->startOfDay(), now()->addDays($before)->endOfDay()])
            ->exists();
    }

    /**
     * Emails written by the automations to this prospect since midnight (project time)
     */
    public static function sentToday(Prospect $prospect, string $timezone): int
    {
        return DB::table('messages')
            ->where('prospect_id', $prospect->id)
            ->where('from_user', 1)
            ->whereNull('deleted_at')
            ->where(fn ($query) => $query->whereNotNull('subject')->orWhereNotNull('email_layout'))
            ->where('created_at', '>=', Carbon::now($timezone)->startOfDay()->utc())
            ->count();
    }

    /**
     * Tomorrow morning, spread over a few minutes so that a burst does not come back at once
     */
    public static function tomorrow(string $timezone): Carbon
    {
        return Carbon::now($timezone)->addDay()->setTime(9, random_int(0, 29))->utc();
    }
}
