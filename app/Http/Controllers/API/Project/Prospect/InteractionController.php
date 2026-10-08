<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Line;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\CloudTalk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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
    public function audio(Project $project, Prospect $prospect, Interaction $interaction, CloudTalk $cloudTalk)
    {
        // Prospect associated to the interaction
        abort_unless($prospect->id == $interaction->prospect_id, 404);
        // abort_unless(auth()->user()->can('prospectInteractionAudio', $project), 404);

        if ($interaction->source === 'cloudtalk') {
            return $this->cloudTalkAudio($project, $interaction, $cloudTalk);
        }

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

        return $interaction->fresh()->load('creator:id,name');
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

    protected function cloudTalkAudio(Project $project, Interaction $interaction, CloudTalk $cloudTalk)
    {
        $callId = $interaction->cloudTalkCallId();

        abort_unless($callId, 404);

        $line = $this->cloudTalkLine($project, $interaction);

        abort_unless($line, 404);

        try {
            $media = $cloudTalk->recordingMedia($line, $callId);
        } catch (RuntimeException $e) {
            abort($this->httpStatus($e->getCode()), $e->getMessage());
        }

        return response($media['body'])
            ->header('Content-Type', $media['content_type'])
            ->header('Content-Disposition', 'inline; filename="cloudtalk-call-' . $callId . '.wav"');
    }

    protected function cloudTalkLine(Project $project, Interaction $interaction): ?Line
    {
        if ($interaction->creator_id) {
            $line = $project
                ->lines()
                ->where('operator', 'cloudtalk')
                ->where('user_id', $interaction->creator_id)
                ->first();

            if ($line) {
                return $line;
            }
        }

        return $project
            ->lines()
            ->where('operator', 'cloudtalk')
            ->where('user_id', auth()->id())
            ->first();
    }

    protected function httpStatus(int $status): int
    {
        return in_array($status, [403, 404, 410, 422, 500, 503])
            ? $status
            : 500;
    }
}
