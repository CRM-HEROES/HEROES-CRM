<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;
use Illuminate\Support\Facades\DB;

class InteractionsCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Appels",
                'key' => "prospect.interaction.new",
                'bgcolor' => "#8979ff",
            ],
        ];
    }
}