<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Project;
use App\Models\Prospect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InteractionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect)
    {
        abort_unless(auth()->user()->can('prospectInteractionView', $project), 404);

        return $prospect
            ->interactions()
            ->with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Prospect $prospect)
    {
        abort_unless(auth()->user()->can('prospectInteractionAdd', $project), 404);

        $interaction = $prospect
            ->interactions()
            ->create(array_merge(
                $request->only(
                    'data',
                    'ended_at',
                    'number',
                    'from_number',
                    'source',
                    'status',
                    'started_at'
                ), [
                    'creator_id' => auth()->id(),
                    'from_user' => 1,
                ],
                $this->storeFile($request, $project)
            ));

        $interaction->load('creator');

        return $interaction;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Prospect $prospect, Interaction $interaction)
    {
        // Prospect associated to the interaction
        abort_unless($prospect->id == $interaction->prospect_id, 404);
        // abort_unless(auth()->user()->can('prospectInteractionView', $project), 404);

        return $interaction;
    }

    /**
     * Return interaction audio.
     */
    public function audio(Project $project, Prospect $prospect, Interaction $interaction)
    {
        // Prospect associated to the interaction
        abort_unless($prospect->id == $interaction->prospect_id, 404);
        // abort_unless(auth()->user()->can('prospectInteractionAudio', $project), 404);

        $disk = Storage::disk('interactions');
        
        abort_unless($interaction->path && $disk->exists($interaction->path), 404);

        return response($disk->get($interaction->path))
            ->header('Content-Type', $disk->mimeType($interaction->path));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Interaction $interaction)
    {
        // Prospect associated to the interaction
        abort_unless($prospect->id == $interaction->prospect_id, 404);
        // abort_unless(auth()->user()->can('prospectInteractionAdd', $project), 404);

        $interaction->update(array_merge(
                $request->only(
                'data',
                'ended_at',
                'number',
                'from_number',
                'source',
                'status',
                'started_at'
            ),
            $this->storeFile($request, $project)
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Interaction $interaction)
    {
        // Prospect associated to the interaction
        abort_unless($prospect->id == $interaction->prospect_id, 404);
        // abort_unless(auth()->user()->can('prospectInteractionAdd', $project), 404);

        $interaction->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store file
     */
    protected function storeFile(Request $request, Project $project)
    {
        if (!$request->hasFile('file')) {
            return [];
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'interactions'),
            'size' => $file->getSize()
        ];
    }
}
