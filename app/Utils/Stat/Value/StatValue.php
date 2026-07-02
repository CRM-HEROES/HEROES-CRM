<?php

namespace App\Utils\Stat\Value;

interface StatValue
{
    /**
     * Get stat unity
     * 
     * @param  content
     */
    public function key();

    /**
     * Get stat value
     * 
     * @param  content
     */
    public function value($projectId);
    
    /**
     * Get stat unity
     * 
     * @param  content
     */
    public function unity();
}