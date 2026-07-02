<?php

namespace App\Filters;

class EventRequestFilters extends EventFilters
{
    use TraitRequestFilters;
    
    protected $params = [
        'ids',
        'withCalendars',
        'withoutCalendars',
        'withProspects',
        'withoutProspects',
        'withProjects',
        'withoutProjects',
        'withCreators',
        'withoutCreators',
        'withUsers',
        'withoutUsers',
        'withConfirmedBy',
        'withoutConfirmedBy',
        'withDoneBy',
        'withoutDoneBy',
        'startedAt',
        'endedAt',
        'startedBeforeTime',
        'startedAfterTime',
        'endedBeforeTime',
        'endedAfterTime',
        'position',
        'confirmed',
        'coming',
        'done',
        'validAddress'
    ];
    
    /**
     * Create a new Filters instance
     */
    public function __construct()
    {
        $this->addRequestFilters($this->params);
    }
}
