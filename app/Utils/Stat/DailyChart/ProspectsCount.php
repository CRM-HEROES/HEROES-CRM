<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;

class ProspectsCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Prospects",
                'key' => "prospect.new",
                'bgcolor' => "#83c7d4"
            ],
        ];
    }
}