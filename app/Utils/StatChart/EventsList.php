<?php

namespace App\Utils\StatChart;

use App\Models\Event;
use App\Models\StatChart;
use App\Utils\StatChart\StatChart as StatChartStatChart;
use Illuminate\Support\Facades\DB;

class EventsList implements StatChartStatChart
{
    private StatChart $statChart;

    public function __construct(StatChart $statChart)
    {
        $this->statChart = $statChart;
    }

    /**
     * Get stat chart data
     * 
     * @param  content
     */
    public function data($beginDate, $endDate)
    {
        $now = \Carbon\Carbon::now();

        // Select all events
        $items = Event
            ::when($this->statChart->project_id, function($query) {
                $query->whereHas('calendar', function($query) {
                    $query->where('project_id', $this->statChart->project_id);
                });
            })
            ->with('calendar:id,name,color,bgcolor,type')
            ->with('confirmedBy:id,name')
            ->with('doneBy:id,name')
            ->with('prospect:id,first_name,last_name,phone_number,mobile_phone_number,latitude,longitude,street,street_bis,postal_code,city,state,county,country')
            ->where(function($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereHas('users', function($query) {
                        $query->where('id', auth()->id());
                    });
            })
            ->whereBetween('started_at', [$now->format("Y-m-d 00:00:00"), $now->format("Y-m-d 23:59:59")])
            ->orderBy('started_at', 'asc')
            ->get();

        return $items;
    }
}