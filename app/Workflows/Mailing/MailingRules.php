<?php

namespace App\Workflows\Mailing;

use App\Models\Prospect;
use App\Workflows\ProjectTimezone;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * The rules a project sets for the emails it sends to its prospects: how many
 * a day, when to keep quiet, who must never be written to.
 *
 * A project has them when its settings hold "mailing_rules" (a JSON, see
 * DEFAULTS): without it, nothing here applies and the emails go as before.
 *
 * Two kinds of email: "marketing" ones (follow-up sequences, newsletters) obey
 * every rule, "service" ones (confirmation of an appointment, answer to a
 * message) only the rules that protect the person: nobody is written to who
 * asked not to be, or who is a minor.
 *
 *   never        minors, unsubscribed, data deletion asked, labels of "do not contact"
 *   postponed    more than max_per_day emails today; a reply waits for a human (task open)
 *   skipped      the days around an appointment (quiet_before_days / quiet_after_days):
 *                the sequences stay silent, the reminders of the appointment are service emails
 *
 * The fields it reads are custom fields of the prospect (their slugs are in
 * the settings): mailing_optout, deletion_requested, task_open.
 */
class MailingRules
{
    public const KEY = 'mailing_rules';

    public const DEFAULTS = [
        'max_per_day' => 1,
        'quiet_before_days' => 3,
        'quiet_after_days' => 1,
        'adult_age' => 18,
        // Custom fields (slugs) of the prospect, read as "oui" when set
        'optout_field' => 'mailing_optout',
        'deletion_field' => 'suppression_demandee',
        'task_field' => 'tache_ouverte',
        // Labels (ids) that mean "do not contact"
        'optout_labels' => [],
    ];

    /**
     * Templates (by code) that leave whatever the rules say: they confirm what the person asked
     * (T05: "your data was deleted")
     */
    public const ALWAYS_SENT = ['T05'];

    public const OPTED_OUT = 'optout';
    public const MINOR = 'minor';
    public const DELETION = 'deletion';
    public const DO_NOT_CONTACT = 'do_not_contact';
    public const QUIET_WINDOW = 'quiet_window';
    public const DISABLED = 'disabled';
    // "if_busy": "skip": a day already has its email (or a reply waits): this one is dropped, not postponed
    public const BUSY = 'busy';

    protected array $cache = [];

    /**
     * The rules of the project, completed; null when the project has none
     */
    public function settings(int $projectId): ?array
    {
        if (!array_key_exists($projectId, $this->cache)) {
            $value = DB::table('project_settings')->where('project_id', $projectId)->where('key', self::KEY)->value('value');
            $value = is_string($value) ? json_decode($value, true) : $value;

            $this->cache[$projectId] = is_array($value) ? array_replace(self::DEFAULTS, $value) : null;
        }

        return $this->cache[$projectId];
    }

    /**
     * Why this prospect must not be written to (a constant of this class), or null.
     * Applies to every email of a project that has rules.
     */
    public function never(Prospect $prospect): ?string
    {
        $rules = $this->settings($prospect->project_id);

        if (!$rules) {
            return null;
        }

        if ($this->isSet($prospect, $rules['optout_field'])) {
            return self::OPTED_OUT;
        }

        if ($this->isSet($prospect, $rules['deletion_field'])) {
            return self::DELETION;
        }

        if ($prospect->date_of_birth && Carbon::parse($prospect->date_of_birth)->age < (int) $rules['adult_age']) {
            return self::MINOR;
        }

        if ($rules['optout_labels'] && DB::table('prospect_label')->where('prospect_id', $prospect->id)->whereIn('label_id', $rules['optout_labels'])->exists()) {
            return self::DO_NOT_CONTACT;
        }

        return null;
    }

    /**
     * Marketing only: the days around an appointment are kept for what concerns it.
     */
    public function quiet(Prospect $prospect): bool
    {
        $rules = $this->settings($prospect->project_id);

        if (!$rules) {
            return false;
        }

        return DB::table('events')
            ->where('prospect_id', $prospect->id)
            ->whereNull('deleted_at')
            ->whereBetween('started_at', [now()->subDays((int) $rules['quiet_after_days'])->startOfDay(), now()->addDays((int) $rules['quiet_before_days'])->endOfDay()])
            ->exists();
    }

    /**
     * Marketing only: when to try again (null: now). One email a day, and none
     * while a reply of the prospect waits for a person.
     */
    public function postponedUntil(Prospect $prospect): ?CarbonInterface
    {
        $rules = $this->settings($prospect->project_id);

        if (!$rules) {
            return null;
        }

        $timezone = ProjectTimezone::for($prospect->project_id);

        if ($this->isSet($prospect, $rules['task_field']) || $this->sentToday($prospect, $timezone) >= (int) $rules['max_per_day']) {
            // Tomorrow morning, spread over a few minutes so that a burst does not come back at once
            return Carbon::now($timezone)->addDay()->setTime(9, random_int(0, 29))->utc();
        }

        return null;
    }

    /**
     * Emails written by the workflows to this prospect since midnight (project time)
     */
    public function sentToday(Prospect $prospect, string $timezone): int
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
     * A custom field of the prospect that holds "oui" (or any value but empty, "non", "0")
     */
    protected function isSet(Prospect $prospect, ?string $slug): bool
    {
        if (!$slug) {
            return false;
        }

        $value = data_get($prospect->meta, $slug);

        return is_scalar($value) && !in_array(strtolower(trim((string) $value)), ['', 'non', 'no', '0', 'false', 'null'], true);
    }
}
