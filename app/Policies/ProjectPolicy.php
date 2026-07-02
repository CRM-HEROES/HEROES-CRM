<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy extends Policy
{
    /**
     * Dynamic function call
     */
    public function __call($method, $args)
    {
        if ($method == '') {
            $permission = "all";
        } else {
            $permission = "all." . $this->camelCaseToLowerDotSeparated($method);
        }

        return $this->can($args[0], $permission);
    }

    /**
     * 
     */
    protected function camelCaseToLowerDotSeparated($input)
    {
        // Use a regular expression to split the camel case string
        $parts = preg_split('/(?=[A-Z])/', $input, -1, PREG_SPLIT_NO_EMPTY);
    
        // Convert the parts to lowercase and join them with dots
        $result = implode('.', array_map('strtolower', $parts));
    
        return $result;
    }
}
