<?php

namespace App\Utils\StatChart;

interface StatChart
{
    /**
     * Get stat chart data
     * 
     * @param  content
     */
    public function data($beginDate, $endDate);
}