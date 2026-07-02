<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;
use App\Models\Project;

class GroupField implements ImportColumnToFieldInterface
{
    protected Project $project;
    protected $groups;
    
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->groups = $project->groups;
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

            $prospect['groups'] = array_map(function($group) {
                return $this->findOrCreateGroup($group)->id;
            }, $data);
        } catch (\Exception $e) {}
    }
    
    /**
     * 
     */
    protected function findOrCreateGroup(&$group)
    {
        if (!($prospectGroup = $this->groups->where('name', $group)->first())) {
            $prospectGroup = $this->project
                ->groups()
                ->create(['name' => $group]);

            $this->groups->push($prospectGroup);
        }
        
        return $prospectGroup;
    }

}