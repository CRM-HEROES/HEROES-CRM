<?php

namespace App\Utils\Stat\DailyChart;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

abstract class StatDailyChart
{
    /**
     * Get stat unity
     * 
     * @param  content
     */
    abstract public function labels();

    /**
     * Get stat value
     * 
     * @param  content
     */
    public function value($project, $from, $to) {
        $values = array_map(function($label) use ($project, $from, $to) {
            return [
                'name' => $label['name'],
                'bgcolor' => $label['bgcolor'],
                'value' => $this->dailyStat($project, $label['key'], $from, $to)
            ];
        }, $this->labels());
        
        $labels = array_map(function($values) {
            return $values['name'];
        }, $values);

        return [
            'labels' => $labels,
            'datasets' => array_map(function($values) {
                return [
                    'label' => $values['name'],
                    'data' => $values['value'],
                    'backgroundColor' => $values['bgcolor'],
                    'borderColor' => isset($values['color']) ? $values['bgcolor'] : $values['bgcolor'],
                    'borderWidth' => 1,
                ];
            }, $values)
        ];
    }

    /**
     * 
     */
    protected function dailyStat(Project $project, $key, $from, $to, $userId = null)
    {
        $results = [];
        
        $previousStatValue = 0;

        if (!$userId) {
            $stat = DB::table('stats')
                ->where('project_id', $project->id)
                ->whereNull('user_id')
                ->where('key', $key)
                ->where('date', '<', $from)
                ->orderBy('date', 'desc')
                ->first();

            $previousStatValue = $stat ? $stat->value : 0;
        }

        $stats = DB::table('stats')
            ->where('project_id', $project->id)
            ->whereNull('user_id')
            ->where('key', $key)
            ->where('date', '>=', $from)
            ->where('date', '<=', $to)
            ->when($userId, function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        for (
            $date = new \Carbon\Carbon($from), 
            $lastDate = new \Carbon\Carbon($to);
            $date->lte($lastDate);
            $date = $date->addDay()
        ) {
            $dateValue = $date->format("Y-m-d");

            if ($stat = $stats->where('date', $dateValue)->first()) {
                $value = $stat->value;
            } else if ($userId) {
                $value = 0;
            } else {
                $value = $previousStatValue;
            }

            $results[$dateValue] = $previousStatValue = $value;
        }

        return $results;
    }

}