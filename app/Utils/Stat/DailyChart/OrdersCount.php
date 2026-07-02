<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;
use Illuminate\Support\Facades\DB;

class OrdersCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Commandes",
                'key' => "prospect.order.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}