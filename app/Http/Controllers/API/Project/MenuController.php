<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\ProspectFilters;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Project;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return $project
            ->menus()
            /*->where(function($query) {
                $query
                    ->whereHas('users', function($query) {
                        $query->where('id', auth()->id());
                    })
                    ->orWhereHas('roles', function($query) {
                        $query->whereIn('id', auth()->user()->roles->pluck('id'));
                    });
            })*/
            ->with('users', function($query) {
                $query
                    ->select('id')
                    ->where('id', auth()->id())
                    ->withPivot('default');
            })
            ->orderBy('order')
            ->get()
            ->transform(function ($menu) use($project) {
                $menu->count = $this->countProspects($project, $menu);
                return $menu;
            });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $menu = $project
            ->menus()
            ->create(array_merge($request->only(
                'name',
                'color',
                'bgcolor',
                'filters',
                'for'
            ), [
                'creator_id' => auth()->id(),
            ]));

        $menu->users()->syncWithoutDetaching(auth()->id());

        return $menu;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Menu $menu)
    {
        abort_unless($project->id == $menu->project_id, 404);

        return $menu;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Menu $menu)
    {
        abort_unless($project->id == $menu->project_id, 404);

        $menu->update($request->only(
            'name',
            'color',
            'bgcolor',
            'filters',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Menu $menu)
    {
        abort_unless($project->id == $menu->project_id, 404);

        $menu->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * 
     */
    protected function countProspects(Project $project, Menu $menu)
    {
        $cacheKey = 
            'project.' . $project->slug . '.' .
            'user.' . (auth()->user() ? auth()->id() : 0) . '.' .
            'menu.' . $menu . '.' .
            'prospects-count';

        return cache()->remember(
            $cacheKey, 
            300, 
            function() use($project, $menu) {
                if (!$menu->filters) {
                    return 0;
                }

                $prospectFilters = new ProspectFilters();
                foreach ($menu->filters as $key => $value) {
                    $prospectFilters->addFilter($key, $value);
                }

                return $project
                    ->prospects()
                    ->filter($prospectFilters)
                    ->count();
            });
    }
}
