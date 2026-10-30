<?php

namespace App\Workflows;

use App\Filters\EventFilters;
use App\Filters\Filters;
use App\Filters\OrderFilters;
use App\Filters\ProspectFilters;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Order;
use App\Models\Prospect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The entities a workflow can run on.
 * Every query is bound to the project of the execution: a workflow of a
 * project can never read or act on the data of another one.
 */
class WorkflowSubject
{
    public const PROSPECT = 'prospect';
    public const ORDER = 'order';
    public const EVENT = 'event';

    public const TYPES = [self::PROSPECT, self::ORDER, self::EVENT];

    /**
     * Query of the subjects of a project
     */
    public static function query(string $type, int $projectId): Builder
    {
        switch ($type) {
            case self::PROSPECT:
                return Prospect::query()->where('prospects.project_id', $projectId);

            case self::ORDER:
                // An order belongs to a project through its prospect
                return Order::query()->whereIn(
                    'orders.prospect_id',
                    Prospect::withoutGlobalScopes()->where('project_id', $projectId)->select('prospects.id')
                );

            case self::EVENT:
                // An appointment belongs to a project through its calendar
                return Event::query()->whereIn(
                    'events.calendar_id',
                    Calendar::withoutGlobalScopes()->where('project_id', $projectId)->select('calendars.id')
                );
        }

        throw new \InvalidArgumentException("Unsupported workflow subject: $type");
    }

    /**
     * Subject, or null when it does not exist (anymore) in the project
     */
    public static function find(string $type, int $id, int $projectId): ?Model
    {
        return self::query($type, $projectId)->whereKey($id)->first();
    }

    /**
     * Filters used to evaluate the conditions on a subject type
     */
    public static function filters(string $type): Filters
    {
        switch ($type) {
            case self::PROSPECT:
                return new ProspectFilters();
            case self::ORDER:
                return new OrderFilters();
            case self::EVENT:
                return new EventFilters();
        }

        throw new \InvalidArgumentException("Unsupported workflow subject: $type");
    }

    /**
     * Subject type a trigger works on: "prospect.created" => "prospect"
     */
    public static function fromTrigger(string $trigger): string
    {
        return explode('.', $trigger, 2)[0];
    }
}
