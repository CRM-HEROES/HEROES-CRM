<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\ThreadRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Thread;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, ThreadRequestFilters $threadRequestFilters)
    {
        return $project
            ->threads()
            ->orderBy('relevance', 'desc')
            ->orderBy('name')
            ->filter($threadRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projectThreadAdd', $project), 404);
        abort_unless(!$request->has('user_id') || $project->users()->find($request->has('user_id')), 404);

        $this->validate($request, [
            'name' => 'required'
        ]);

        return $project
            ->threads()
            ->create(array_merge($request->only(
                'bgcolor',
                'color',
                'name',
                'private',
                'send_to',
                'user_id'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Thread $thread)
    {
        abort_unless($project->id == $thread->project_id, 404);

        return $thread;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Thread $thread)
    {
        abort_unless(auth()->user()->can('projectThreadUpdate', $project), 404);
        abort_unless($project->id == $thread->project_id, 404);
        abort_unless(!$request->has('user_id') || $project->users()->find($request->has('user_id')), 404);

        $thread->update($request->only(
            'name',
            'private',
            'bgcolor',
            'color',
            'user_id',
            'order'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Thread $thread)
    {
        abort_unless(auth()->user()->can('projectThreadDelete', $project), 404);
        abort_unless($project->id == $thread->project_id, 404);

        $thread->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
