<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        return $prospect
            ->links()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        return $prospect
            ->links()
            ->create(array_merge($request->only(
                'name',
                'link'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Link $link)
    {
        abort_unless($prospect->id == $link->prospect_id, 404);

        return $link;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Link $link)
    {
        abort_unless($prospect->id == $link->prospect_id, 404);

        $link->update($request->only(
            'name',
            'link'
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Link $link)
    {
        abort_unless($prospect->id == $link->prospect_id, 404);

        $link->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
