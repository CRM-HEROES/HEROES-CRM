<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;
use Illuminate\Support\Facades\DB;

class OrdersTotal extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Total des commandes",
                'key' => "prospect.order.value.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}