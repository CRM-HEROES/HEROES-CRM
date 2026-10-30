<?php

namespace App\Filters;

use App\Models\Calendar;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

class EventFilters extends Filters
{
    /**
     * Filter by id
     */
    public function ids(Builder $builder, $value) {
        $builder->whereIn('id', $value);
    }

    /**
     * Filter by calendars
     */
    public function withCalendars(Builder $builder, $value) {
        if (!empty($value)) {
            $builder->whereIn('calendar_id', $value);
        } else {
            $builder->whereNotNull('calendar_id');
        }
    }

    /**
     * Filter by without calendars
     */
    public function withoutCalendars(Builder $builder, $value) {
        if (!empty($value)) {
            $builder->whereNotIn('calendar_id', $value);
        } else {
            $builder->whereNull('calendar_id');
        }
    }

    /**
     * Filter by creator
     */
    public function withCreators(Builder $builder, $value) {
        return $builder->whereHas('creator', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by without creator
     */
    public function withoutCreators(Builder $builder, $value) {
        return $builder->whereDoesntHave('creators', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by prospect
     */
    public function withProspects(Builder $builder, $value) {
        return $builder->whereHas('prospect', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $prospectFilters = new ProspectFilters();

                foreach ($value as $k => $v) {
                    $prospectFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by without prospects
     */
    public function withoutProspects(Builder $builder, $value) {
        return $builder->whereDoesntHave('prospects', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $prospectFilters = new ProspectFilters();

                foreach ($value as $k => $v) {
                    $prospectFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by projects
     *
     * The calendars are selected with an IN sub-query instead of a correlated EXISTS,
     * so MySQL can use the (calendar_id, ...) indexes of events.
     * The Calendar global scopes still apply to the sub-query.
     */
    public function withProjects(Builder $builder, $value) {
        return $builder->whereIn('events.calendar_id', Calendar::whereIn('project_id', $value)->select('calendars.id'));
    }

    /**
     * Filter by without projects
     */
    public function withoutProjects(Builder $builder, $value) {
        return $builder->whereNotIn('events.calendar_id', Calendar::whereIn('project_id', $value)->select('calendars.id'));
    }

    /**
     * Filter by user (affected user or attendee)
     *
     * user_id IN (...) OR id IN (attendees sub-query): two indexable lookups
     * instead of two correlated EXISTS combined with an OR.
     */
    public function withUsers(Builder $builder, $value) {
        if ($value === []) {
            return $builder->whereRaw('1 = 0');
        }

        $ids = Arr::flatten((array) $value);

        return $builder->where(function($query) use($ids) {
            if (empty($ids)) {
                $query
                    ->whereNotNull('events.user_id')
                    ->orWhereIn('events.id', DB::table('user_event')->select('event_id'));
                return;
            }

            $query
                ->whereIn('events.user_id', $ids)
                ->orWhereIn('events.id', DB::table('user_event')->whereIn('user_id', $ids)->select('event_id'));
        });
    }

    /**
     * Filter by without user
     */
    public function withoutUsers(Builder $builder, $value) {
        $ids = Arr::flatten((array) $value);

        return $builder->where(function($query) use($ids) {
            $query->whereNull('events.user_id');

            if (!empty($ids)) {
                $query->orWhereNotIn('events.user_id', $ids);
            }
        });
    }

    /**
     * Filter by confirmed by user
     */
    public function withConfirmedBy(Builder $builder, $value) {
        return $builder->whereHas('confirmedBy', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by without confirmed by user
     */
    public function withoutConfirmedBy(Builder $builder, $value) {
        return $builder->whereDoesntHave('confirmedBy', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by done by user
     */
    public function withDoneBy(Builder $builder, $value) {
        return $builder->whereHas('doneBy', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by without done by user
     */
    public function withoutDoneBy(Builder $builder, $value) {
        return $builder->whereDoesntHave('doneBy', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $userFilters = new UserFilters();

                foreach ($value as $k => $v) {
                    $userFilters->{$k}($query, $v);
                }
            });
        });
    }

    /**
     * Filter by started at
     */
    public function startedAt(Builder $builder, $value) {
        $builder->where('started_at', '>=', substr($value, 0, 10) . " 00:00:00");
    }

    /**
     * Filter by starts in more than (seconds)
     */
    public function startsInMoreThan(Builder $builder, $value) {
        $builder->where('started_at', '>=', \Carbon\Carbon::now()->addSeconds($value)->format("Y-m-d H:i:s"));
    }

    /**
     * Filter by starts in less than (seconds)
     */
    public function startsInLessThan(Builder $builder, $value) {
        $builder->where('started_at', '<=', \Carbon\Carbon::now()->addSeconds($value)->format("Y-m-d H:i:s"));
    }

    /**
     * Filter by started more than (seconds)
     */
    public function startedMoreThan(Builder $builder, $value) {
        $builder->where('started_at', '<=', \Carbon\Carbon::now()->subSeconds($value)->format("Y-m-d H:i:s"));
    }

    /**
     * Filter by started less than (seconds)
     */
    public function startedLessThan(Builder $builder, $value) {
        $builder->where('started_at', '>=', \Carbon\Carbon::now()->subSeconds($value)->format("Y-m-d H:i:s"));
    }

    /**
     * Filter by started before
     */
    public function startedBefore(Builder $builder, $value) {
        $builder->where('started_at', '<', $value . " 00:00:00");
    }

    /**
     * Filter by ended at
     */
    public function endedAt(Builder $builder, $value) {
        $builder->where('ended_at', '<=', substr($value, 0, 10) . " 23:59:59");
    }

    /**
     * Include events intersecting an agenda range, including overnight events.
     */
    public function overlapping(Builder $builder, $value) {
        $range = Validator::make(['overlapping' => $value], [
            'overlapping' => ['required', 'array:startedAt,endedAt'],
            'overlapping.startedAt' => ['required', 'date_format:Y-m-d'],
            'overlapping.endedAt' => ['required', 'date_format:Y-m-d', 'after_or_equal:overlapping.startedAt'],
        ])->stopOnFirstFailure()->validate()['overlapping'];

        $builder->where('started_at', '<=', $range['endedAt'] . ' 23:59:59')
            ->where('ended_at', '>=', $range['startedAt'] . ' 00:00:00');
    }
    
    /**
     * Filter by started before time
     */
    public function startedBeforeTime(Builder $builder, $value) {
        $builder->whereTime('started_at', '<', $value);
    }

    /**
     * Filter by started after time
     */
    public function startedAfterTime(Builder $builder, $value) {
        $builder->whereTime('started_at', '>=', $value);
    }

    /**
     * Filter by ended before time
     */
    public function endedBeforeTime(Builder $builder, $value) {
        $builder->whereTime('ended_at', '<=', $value);
    }

    /**
     * Filter by ended after time
     */
    public function endedAfterTime(Builder $builder, $value) {
        $builder->whereTime('ended_at', '>', $value);
    }

    /**
     * Filter by coming
     */
    public function coming(Builder $builder, $value) {
        $builder->where('started_at', $value ? '>' : '<=', \Carbon\Carbon::now()->format("Y-m-d H:i:s"));
    }

    /**
     * Filter by confirmed
     */
    public function confirmed(Builder $builder, $value) {
        $builder->where('confirmed', $value);
    }
    
    /**
     * Filter by done
     */
    public function done(Builder $builder, $value) {
        $builder->where('done', $value);
    }
    
    /**
     * Filter by valid address
     */
    public function validAddress(Builder $builder, $value) {
        if ($value == 1) {
            return $builder->whereNotNull('latitude')->whereNotNull('longitude');
        }

        return $builder->where(function($query) {
            $query->whereNull('latitude')->orWhereNull('longitude');
        });
    }

    /**
     * Search by position
     */
    public function position($builder, $value) {
        list($lat, $lng, $km) = explode(',', $value);

        if (empty($lat) || empty($lng) || empty($km)) {
            return $builder;
        }
    
        $raw = "(111.111 *" .
            " DEGREES(ACOS(LEAST(1.0, COS(RADIANS($lat))" .
            " * COS(RADIANS(events.latitude))" .
            " * COS(RADIANS($lng - events.longitude))" .
            " + SIN(RADIANS($lat))" .
            " * SIN(RADIANS(events.latitude))))))";

        return $builder->where(DB::raw($raw), '<=', $km);
    }
}
