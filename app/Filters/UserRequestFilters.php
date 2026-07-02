<?php

namespace App\Filters;

class UserRequestFilters extends UserFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'query',
        'ids',
        'withProjects',
        'withoutProjects',
        'withCalendars',
        'withoutCalendars',
        'withCategories',
        'withoutCategories',
        'withDocuments',
        'withoutDocuments',
        'withFolders',
        'withoutFolders',
        'withGroups',
        'withoutGroups',
        'withMenus',
        'withoutMenus',
        'withOrderActions',
        'withoutOrderActions',
        'withOrderSteps',
        'withoutOrderSteps',
        'withProspects',
        'withoutProspects',
        'withRoles',
        'withoutRoles',
        'withThreads',
        'withoutUsers',
        'withUsers',
        'withoutThreads',
        'withVehicles',
        'withoutVehicles',
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $fields = [
            'name',
            'last_name',
            'email',
            'street',
            'street_bis',
            'postal_code',
            'country',
            'city',
        ];

        // Fields
        foreach ($fields as $field) {
            $this->params[] = 'field_' . $field;
        }

        $this->addRequestFilters($this->params);
    }
}
