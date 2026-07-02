<?php

namespace App\Filters;

class ThreadRequestFilters extends ThreadFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
        'private',
        'sendTo',
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
