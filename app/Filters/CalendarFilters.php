<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CalendarFilters extends Filters
{
    /**
     * Filter by id
     */
    protected function ids(Builder $builder, $value) {
        $value = explode(',', $value);

        $builder->whereIn('id', $value);
    }

    /**
     * Filter by creators
     */
    protected function creators(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereIn('creator_id', $value);
    }
    
    /**
     * Filter by prospects
     */
    protected function types(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereIn('type', $value);
    }

    /**
     * Filter by query
     */
    protected function query(Builder $builder, $value)
    {
        $builder->where(DB::raw("LOWER(`name`)"), 'LIKE', "%{$value}%");
    }
    
    /**
     * Filter by projects
     */
    public function withProjects(Builder $builder, $value) {
        return $builder->whereHas('project', function($query) use($value) {
            $query->whereIn('project_id', $value);
        });
    }

    /**
     * Filter by without projects
     */
    public function withoutProjects(Builder $builder, $value) {
        return $builder->whereDoesntHave('prospects', function($query) use($value) {
            $query->whereIn('project', $value);
        });
    }
}
