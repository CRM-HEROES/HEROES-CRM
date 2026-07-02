<?php

namespace App\Console\Commands\Relevance;

abstract class UserProjectRelevance
{
    /**
     * 
     */
    abstract public function getData($from);
    
    /**
     * 
     */
    public function getField()
    {
        return "relevance";
    }
    
    /**
     * 
     */
    abstract public function getName();
}