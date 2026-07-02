<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;
use Illuminate\Support\Facades\DB;

class InteractionsDuration extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Durée des appels",
                'key' => "prospect.interaction.duration.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}