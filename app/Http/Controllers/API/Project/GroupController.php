<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\GroupRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Project;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, GroupRequestFilters $groupRequestFilters)
    {
        return $project
            ->groups()
            ->orderBy('relevance', 'desc')
            ->orderBy('name')
            ->filter($groupRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectGroupAdd', $project), 404);

        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->groups()
            ->create(array_merge($request->only(
                'name',
                'color',
                'bgcolor'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Group $group)
    {
        abort_unless($project->id == $group->project_id, 404);

        return $group;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Group $group)
    {
        abort_unless(auth()->user()->can('projectGroupUpdate', $project), 404);
        abort_unless($project->id == $group->project_id, 404);

        $group->update($request->only(
            'name',
            'color',
            'bgcolor',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Group $group)
    {
        abort_unless(auth()->user()->can('projectGroupDelete', $project), 404);
        abort_unless($project->id == $group->project_id, 404);

        $group->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
