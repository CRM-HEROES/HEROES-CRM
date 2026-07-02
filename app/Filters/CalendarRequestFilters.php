<?php

namespace App\Filters;

class CalendarRequestFilters extends CalendarFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
        'types',
        'query',
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $this->addRequestFilters($this->params);
    }
}
