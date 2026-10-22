<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\ProspectFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;

class DefaultMenuController extends Controller
{
    /**
     * Seconds the menu counters are kept in cache (per project and per user).
     */
    protected const COUNT_TTL = 60;

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

        // not-trashed / processed / not-processed only differ by processed_at:
        // one aggregated query instead of three COUNT on the prospects table.
        $processedCounts = $this->countProcessed($project);

        return array_map(function($menu, $filters) use($project, $processedCounts) {
            return [
                'id' => $menu,
                'name' => trans('menu.' . $menu),
                'filters' => $filters,
                'color' => "#000000",
                'bgcolor' => "#FFFFFF",
                'count' => $processedCounts[$menu] ?? $this->countProspects($project, $menu, $filters)
            ];
        }, array_keys($menus), array_values($menus));
    }

    /**
     * Counters of the menus that only depend on prospects.processed_at.
     * The query goes through the Prospect global scopes (soft deletes and
     * visibility of the authenticated user), like the filtered COUNT did.
     */
    protected function countProcessed(Project $project): array
    {
        return cache()->remember(
            $this->cacheKey($project, 'processed-counts'),
            static::COUNT_TTL,
            function() use($project) {
                $row = $project
                    ->prospects()
                    ->toBase()
                    ->selectRaw('COUNT(*) AS total, COALESCE(SUM(processed_at IS NOT NULL), 0) AS processed')
                    ->first();

                $total = (int) $row->total;
                $processed = (int) $row->processed;

                return [
                    'not-trashed' => $total,
                    'processed' => $processed,
                    'not-processed' => $total - $processed,
                ];
            }
        );
    }

    /**
     * Count the prospects of a menu
     */
    protected function countProspects(Project $project, $menu, $filters)
    {
        return cache()->remember(
            $this->cacheKey($project, 'menu.' . $menu),
            static::COUNT_TTL,
            function() use($project, $filters) {
                $prospectFilters = new ProspectFilters();
                foreach ($filters as $key => $value) {
                    $prospectFilters->addFilter($key, $value);
                }

                return $project
                    ->prospects()
                    ->filter($prospectFilters)
                    ->count();
            }
        );
    }

    protected function cacheKey(Project $project, $suffix): string
    {
        return 'project.' . $project->id . '.' .
            'user.' . (auth()->user() ? auth()->id() : 0) . '.' .
            'default-menu.' . $suffix;
    }
}
