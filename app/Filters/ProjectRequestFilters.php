<?php

namespace App\Filters;

class ProjectRequestFilters extends ProjectFilters
{
    use TraitRequestFilters;

    protected $params = [
        'ids',
        'users',
        'position',
        'query',
        'type',
        'admin',
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $fields = [
            'name',
            'phone_number',
            'email',
            'street',
            'postal_code',
            'city',
            'country', 
            'num_tva_intra',
            'naf',
            'siret',
            'capital',
        ];

        // Fields
        foreach ($fields as $field) {
            $this->params[] = 'field_' . $field;
        }

        $this->addRequestFilters($this->params);
    }
}
