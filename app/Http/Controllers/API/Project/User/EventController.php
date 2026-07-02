<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Event $event)
    {
        abort_unless($project->id == $event->calendar->project_id, 404);
        abort_unless(auth()->user()->can('', $project) || $event->user_id == auth()->id() || $event->creator_id == auth()->id(), 404);

        $event
            ->users()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$user->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        if ($event->prospect_id) {
            $user->prospects()->syncWithoutDetaching($event->prospect_id);
        }

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Event $event)
    {
        abort_unless($project->id == $event->calendar->project_id, 404);
        abort_unless(auth()->user()->can('', $project) || $event->user_id == auth()->id() || $event->creator_id == auth()->id(), 404);

        $event->users()->detach($user->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
