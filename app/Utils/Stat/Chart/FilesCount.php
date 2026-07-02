<?php

namespace App\Utils\Stat\Chart;

use App\Utils\Stat\Chart\StatChart;
use Illuminate\Support\Facades\DB;

class FilesCount implements StatChart
{

    public function key()
    {
        return "stat.chart.file.count";
    }

    public function value($project)
    {
        return cache()->remember(
            $this->key(), 
            3600, 
            function() use ($project) {
                $files = DB::table('files')
                    ->select('files.folder_id', DB::raw('count(*) as files_count'))
                    ->join('folders', 'folders.id', '=', 'files.folder_id')
                    ->where('folders.project_id', $project->id)
                    ->groupBy('files.folder_id')
                    ->get();

                $labelValues = DB::table('folders')
                    ->whereIn('id', $files->map(function($v) {
                        return $v->folder_id;
                    }))
                    ->get(['id', 'name', 'bgcolor']);

                $files = $files->map(function($file) use($labelValues) {
                        $label = $labelValues->where('id', $file->folder_id)->first();
    
                        return array_merge((array) $label, [
                            'count' => $file->files_count
                        ]);
                    });

                $labels = $files->map(function($file) {
                    return $file['name'];
                });

                $data = $files->map(function($file) {
                    return $file['count'];
                });

                $backgroundColor = $files->map(function($file) {
                    return $file['bgcolor'];
                });
    
                return [
                    'labels' => $labels,
                    'datasets' => [[
                        'data' => $data,
                        'backgroundColor' => $backgroundColor
                    ] ]
                ];
            });
    }
}