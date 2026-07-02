<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\ProspectFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;

class DefaultMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $menus = [
            'not-trashed' => [],
            // 'all' => ['all' => 1],
            // 'trashed' => ['trashed' => 1],
            'processed' => ['processed' => 1],
            'not-processed' => ['processed' => 0],
            'waiting' => ['waitingUsers' => [auth()->id()]],
            // 'archived-waiting' => ['archivedWaitingUsers' => [auth()->id()]],
            // 'with-events' => ['withEvents' => []],
            // 'without-events' => ['withoutEvents' => []],
        ];
        
        return array_map(function($menu, $filters) use($project) {
            return [
                'id' => $menu,
                'name' => trans('menu.' . $menu),
                'filters' => $filters,
                'color' => "#000000",
                'bgcolor' => "#FFFFFF",
                'count' => $this->countProspects($project, $menu, $filters)
            ];
        }, array_keys($menus), array_values($menus));
    }

    /**
     * 
     */
    protected function countProspects(Project $project, $menu, $filters)
    {
        $cacheKey = 
            'project.' . $project->slug . '.' .
            'user.' . (auth()->user() ? auth()->id() : 0) . '.' .
            'menu.' . $menu . '.' .
            'prospects-count';

        /*return cache()->remember(
            $cacheKey, 
            1, 
            function() use($project, $filters) {*/
                $prospectFilters = new ProspectFilters();
                foreach ($filters as $key => $value) {
                    $prospectFilters->addFilter($key, $value);
                }

                return $project
                    ->prospects()
                    ->filter($prospectFilters)
                    ->count();
            // });
    }
}
