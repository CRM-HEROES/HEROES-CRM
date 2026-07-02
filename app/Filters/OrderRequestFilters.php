<?php

namespace App\Filters;

class OrderRequestFilters extends OrderFilters
{
    use TraitRequestFilters;

    protected $params = [
        'ids',
        'creators',
        'query',
        'withProspects',
        'withoutProspects',
        'withProducts',
        'withoutProducts',
        'withStatuses',
        'withoutStatuses',
        'withSteps',
        'withoutSteps',
        'withActions',
        'withoutActions',
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $fields = [
            'name',
            'description',
        ];

        // Fields
        foreach ($fields as $field) {
            $this->params[] = 'field_' . $field;
        }

        $this->addRequestFilters($this->params);
    }
}
