<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ImportFilters extends Filters
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
     * Filter by source
     */
    protected function sources(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereIn('source', $value);
    }

    /**
     * Filter by query
     */
    protected function query(Builder $builder, $value)
    {
        $builder->where(DB::raw("LOWER(`name`)"), 'LIKE', "%{$value}%");
    }
}
