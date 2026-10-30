<?php

namespace App\Workflows;

use Carbon\CarbonInterface;

/**
 * Date and time conditions. They do not depend on the subject: they are
 * evaluated once, in PHP, and folded into the SQL condition as a constant.
 *
 *  work_hours  { "days": [1,2,3,4,5], "from": "08:00", "to": "17:00", "timezone"?: "Europe/Paris" }
 *              days: ISO weekdays (1 = Monday ... 7 = Sunday). "to" is exclusive and may
 *              be "24:00". When "from" is after "to" the period crosses midnight
 *              (22:00 -> 06:00) and belongs to the day it started on.
 *  date_range  { "from": "2026-12-24", "to": "2026-12-26", "timezone"?: ... } (inclusive)
 *
 * The timezone is the one of the node, else the one of the project: never
 * the one of the server.
 *
 * Public holidays are not handled: there is no calendar of them in the CRM yet.
 */
class TimeChecks
{
    public const WORK_HOURS = 'work_hours';
    public const DATE_RANGE = 'date_range';

    public const CHECKS = [self::WORK_HOURS, self::DATE_RANGE];

    public static function evaluate(string $check, array $config, int $projectId, CarbonInterface $now): bool
    {
        $timezone = $config['timezone'] ?? ProjectTimezone::for($projectId);
        $local = $now->copy()->setTimezone($timezone);

        return match ($check) {
            self::WORK_HOURS => self::workHours($config, $local),
            self::DATE_RANGE => self::dateRange($config, $local),
            default => false,
        };
    }

    public static function workHours(array $config, CarbonInterface $local): bool
    {
        $days = $config['days'] ?? [1, 2, 3, 4, 5, 6, 7];
        $from = self::minutes($config['from'] ?? '00:00');
        $to = self::minutes($config['to'] ?? '24:00');
        $minutes = $local->hour * 60 + $local->minute;

        if ($from === null || $to === null || $from === $to) {
            return false;
        }

        $today = $local->isoWeekday();
        $yesterday = $today === 1 ? 7 : $today - 1;

        if ($from < $to) {
            return in_array($today, $days) && $minutes >= $from && $minutes < $to;
        }

        // Crosses midnight
        return ($minutes >= $from && in_array($today, $days))
            || ($minutes < $to && in_array($yesterday, $days));
    }

    public static function dateRange(array $config, CarbonInterface $local): bool
    {
        $date = $local->format('Y-m-d');

        return (empty($config['from']) || $date >= $config['from'])
            && (empty($config['to']) || $date <= $config['to']);
    }

    /**
     * "HH:MM" => minutes since midnight ("24:00" is allowed), null if invalid
     */
    public static function minutes(?string $time): ?int
    {
        if ($time === '24:00') {
            return 1440;
        }

        if (!is_string($time) || !preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $m)) {
            return null;
        }

        return (int) $m[1] * 60 + (int) $m[2];
    }
}
