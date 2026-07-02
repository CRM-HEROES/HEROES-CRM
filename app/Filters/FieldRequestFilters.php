<?php

namespace App\Filters;

class FieldRequestFilters extends FieldFilters
{
    use TraitRequestFilters;

    protected $params = [
        'ids',
        'creators',
        'for',
        'type',
        'meta',
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
        ];

        // Fields
        foreach ($fields as $field) {
            $this->params[] = 'field_' . $field;
        }

        $this->addRequestFilters($this->params);
    }
}
