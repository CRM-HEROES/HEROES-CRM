<?php

namespace App\Utils\Stat\Chart;

interface StatChart
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
}