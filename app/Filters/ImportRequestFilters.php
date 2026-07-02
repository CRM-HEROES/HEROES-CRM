<?php

namespace App\Filters;

class ImportRequestFilters extends ImportFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
        'sources',
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
