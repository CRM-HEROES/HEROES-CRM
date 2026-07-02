<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class Filters
{
    
    /**
     * Registered filters to operate upond.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * To apply before all filters
     */
    protected function beforeFilters(Builder $builder) {}

    /**
     * To apply after all filters
     */
    protected function afterFilters(Builder $builder) {}

    /**
     * Append filter
     */
    public function addFilter($filter, $value) {
        $this->filters[$filter] = $value;
    }

    /**
     * Get filter
     */
    public function getFilter($filter) {
        return isset($this->filters[$filter]) ? $this->filters[$filter] : null;
    }

    /**
     * Append filters
     */
    public function addFilters($filters) {
        foreach ($filters as $filter => $value) {
            $this->addFilter($filter, $value);
        }
    }

    /**
     * Apply the filters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder)
    {
        $this->beforeFilters($builder);

        foreach ($this->filters as $filter => $value) {
            $this->$filter($builder, $value);
        }

        $this->afterFilters($builder);

        return $builder;
    }
}
