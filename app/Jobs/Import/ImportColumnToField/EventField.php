<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;
use App\Models\Project;

class EventField implements ImportColumnToFieldInterface
{
    protected Project $project;
    protected $calendars;
    protected $userRepository;
    
    public function __construct(Project $project, UserRepository $userRepository)
    {
        $this->project = $project;
        $this->calendars = $project->calendars;
        $this->userRepository = $userRepository;
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        try {
            $data = json_decode($value, true);

            if (!is_array($data)) {
                return;
            }

            $prospect['events'] = array_map(function($event) {
                $eventUser = $event['user'] ? 
                    $this->userRepository->findOrCreateUser($event['user']) : 
                    null;

                $eventCalendar = $this->findOrCreateCalendar($event['calendar']);

                return [
                    'name'        => data_get($event, 'name'),
                    'description' => data_get($event, 'description'),
                    'started_at'  => data_get($event, 'started_at'),
                    'ended_at'    => data_get($event, 'ended_at'),
                    'location'    => data_get($event, 'location'),
                    'latitude'    => data_get($event, 'latitude'),
                    'longitude'   => data_get($event, 'longitude'),
                    'user_id'     => $eventUser ? $eventUser->id : null,
                    'calendar_id' => $eventCalendar->id,
                ];
            }, $data);
        } catch (\Exception $e) {}
    }
    
    /**
     * 
     */
    protected function findOrCreateCalendar(&$calendar)
    {
        // event calendar
        if (!($eventCalendar = $this->calendars->where('name', $calendar['name'])->first())) {
            $eventCalendar = $this->project
                ->calendars()
                ->create($calendar);

            $this->calendars->push($eventCalendar);
        }
        
        return $eventCalendar;
    }

}