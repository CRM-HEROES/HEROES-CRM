<?php

namespace App\Filters;

class ProductRequestFilters extends ProductFilters
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
        $fields = [
            'name',
            'description',
            'price',
            'tax',
            'currency',
        ];

        // Fields
        foreach ($fields as $field) {
            $this->params[] = 'field_' . $field;
        }

        $this->addRequestFilters($this->params);
    }
}
