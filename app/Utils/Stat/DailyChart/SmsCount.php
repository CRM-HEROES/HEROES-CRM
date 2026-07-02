<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;

class SmsCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "SMS",
                'key' => "prospect.sms.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}