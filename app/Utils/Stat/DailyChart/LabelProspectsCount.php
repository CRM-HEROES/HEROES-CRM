<?php

namespace App\Utils\Stat\DailyChart;

use App\Utils\Stat\DailyChart\StatDailyChart;

class LabelProspectsCount extends StatDailyChart
{
    protected $l;

    /**
     *
     * @return void
     */
    public function __construct($labels)
    {
        $this->l = $labels;
    }

    public function labels()
    {
        return array_map(function ($label) {
            return array_merge($label, [
                'key' => "label.total." . $label['key'],
            ]);
        }, $this->l);
    }
}