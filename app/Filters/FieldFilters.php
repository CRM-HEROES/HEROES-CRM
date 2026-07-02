<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FieldFilters extends Filters
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
     * Filter by for
     */
    protected function for(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereIn('for', $value);
    }

    /**
     * Filter by type
     */
    protected function type(Builder $builder, $value)
    {
        $value = explode(',', $value);

        $builder->whereIn('type', $value);
    }

    /**
     * Filter by meta
     */
    protected function meta(Builder $builder, $value)
    {
        if ($value) {
            $builder->where('meta', true);
        } else {
            $builder->where('meta', false);
        }
    }

    /**
     * Filter by query
     */
    protected function query(Builder $builder, $value)
    {
        $value = Str::lower($value);
        $builder->where(function ($query) use($value) {
            foreach (['name', 'description'] as $field) {
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
