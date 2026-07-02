<?php

namespace App\Filters;

class GroupRequestFilters extends GroupFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
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
