<?php

namespace App\Filters;

class FolderRequestFilters extends FolderFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'creators',
        'private',
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
