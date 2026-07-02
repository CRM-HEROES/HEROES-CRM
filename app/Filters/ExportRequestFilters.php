<?php

namespace App\Filters;

class ExportRequestFilters extends ExportFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
        'prospects',
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $this->addRequestFilters($this->params);
    }
}
