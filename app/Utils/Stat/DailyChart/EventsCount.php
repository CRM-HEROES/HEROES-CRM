<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;

class EventsCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "RDV",
                'key' => "prospect.event.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}