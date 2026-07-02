<?php

namespace App\Filters;

class CategoryRequestFilters extends CategoryFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
        'for',
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
