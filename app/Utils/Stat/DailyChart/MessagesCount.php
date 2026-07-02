<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;
use Illuminate\Support\Facades\DB;

class MessagesCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Messages",
                'key' => "prospect.message.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}