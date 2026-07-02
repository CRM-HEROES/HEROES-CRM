<?php

namespace App\Filters;

class CommissionRequestFilters extends CommissionFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'order',
        'actions',
        'products',
        'users',
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $this->addRequestFilters($this->params);
    }
}
