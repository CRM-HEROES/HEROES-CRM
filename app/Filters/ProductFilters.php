<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductFilters extends Filters
{
    /**
     * Filter by id
     */
    protected function ids(Builder $builder, $value) {
        $builder->whereIn('id', $value);
    }

    /**
     * Filter by creators
     */
    protected function creators(Builder $builder, $value)
    {
        $builder->whereIn('creator_id', $value);
    }

    /**
     * Filter by query
     */
    protected function query(Builder $builder, $value)
    {
        $value = Str::lower($value);
        $builder->where(function ($query) use($value) {
            foreach (['name', 'description', 'currency'] as $field) {
                return $query->where(DB::raw("LOWER(`$field`)"), 'LIKE', "%{$value}%");
            }
        });
    }

    /**
     * Dynamic function call
     */
    public function __call($method, $args)
    {
        // Default field search
        if (Str::startsWith($method, 'field_')) {
            return $this->defaultField(
                $args[0],
                Str::replace('field_', '', $method),
                $args[1]
            );
        }
        
        throw new \BadMethodCallException("Method $method does not exist");;
    }
    
    /**
     * Search by default field
     */
    protected function defaultField($builder, $slug, $value)
    {
        $value = Str::lower($value);
        return $builder->where(DB::raw("LOWER(`$slug`)"), 'LIKE', "%{$value}%");
    }
}
