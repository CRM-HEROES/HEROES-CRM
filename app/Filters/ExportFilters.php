<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ExportFilters extends Filters
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
    protected function prospects(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->where(function($query) use($value) {
            return array_reduce($value, function($query, $v) {
                return $query->orWhereJsonContains('prospects', intval($v));
            }, $query);
        });
    }
}
