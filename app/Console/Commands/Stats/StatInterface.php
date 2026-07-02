<?php

namespace App\Console\Commands\Stats;

interface StatInterface
{
    /**
     * 
     */
    public function key();
    
    /**
     * 
     */
    public function value($date, $userId = null);
}