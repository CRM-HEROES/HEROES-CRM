<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;
use Illuminate\Support\Facades\DB;

class FilesCount extends StatDailyChart
{
    public function labels()
    {
        return [
            [
                'name' => "Fichiers",
                'key' => "prospect.file.new",
                'bgcolor' => "#ffae4c"
            ],
        ];
    }
}