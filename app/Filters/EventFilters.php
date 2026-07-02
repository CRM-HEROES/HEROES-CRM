<?php

namespace App\Filters;

use Illuminate\Support\Facades\DB;
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
     */
    public function withProjects(Builder $builder, $value) {
        return $builder->whereHas('calendar', function($query) use($value) {
            $query->whereIn('project_id', $value);
        });
    }

    /**
     * Filter by without projects
     */
    public function withoutProjects(Builder $builder, $value) {
        return $builder->whereDoesntHave('calendar', function($query) use($value) {
            $query->whereIn('project_id', $value);
        });
    }

    /**
     * Filter by user
     */
    public function withUsers(Builder $builder, $value) {
        return $builder->where(function($query) use($value) {
            $query
                ->whereHas('user', function($query) use($value) {
                        $query->when(!empty($value), function($query) use($value) {
                            $query->whereIn('id', $value);
                    });
                })
                ->orWhereHas('users', function($query) use($value) {
                        $query->when(!empty($value), function($query) use($value) {
                            $query->whereIn('id', $value);
                    });
                });
        });
    }

    /**
     * Filter by without user
     */
    public function withoutUsers(Builder $builder, $value) {
        return $builder->whereDoesntHave('user', function($query) use($value) {
            $query->when(!empty($value), function($query) use($value) {
                $query->whereIn('id', $value);
            });
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
        $builder->where('started_at', '>=', \Carbon\Carbon::now()->addSeconds($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by starts in less than (seconds)
     */
    public function startsInLessThan(Builder $builder, $value) {
        $builder->where('started_at', '<=', \Carbon\Carbon::now()->addSeconds($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by started more than (seconds)
     */
    public function startedMoreThan(Builder $builder, $value) {
        $builder->where('started_at', '<=', \Carbon\Carbon::now()->subSeconds($value)->format("Y-m-d h:i:s"));
    }

    /**
     * Filter by started less than (seconds)
     */
    public function startedLessThan(Builder $builder, $value) {
        $builder->where('started_at', '>=', \Carbon\Carbon::now()->subSeconds($value)->format("Y-m-d h:i:s"));
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
        $builder->where('started_at', $value ? '>' : '<=', \Carbon\Carbon::now()->format("Y-m-d h:i:s"));
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
