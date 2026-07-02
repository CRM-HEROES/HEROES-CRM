<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Jobs\UserProspectMessage;
use App\Models\Message;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;

class MessageUserController extends Controller
{
    /**
     * Attach user to a given message
     */
    public function update(Request $request, Project $project, Prospect $prospect, Thread $thread, Message $message, User $user)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);

        // Attach the user
        // to the message
        $user
            ->messages()
            ->withPivot('creator_id', 'created_at', 'updated_at', 'archived_at')
            ->syncWithoutDetaching([$message->id => array_merge($request->only('archived_at'), [
                'updated_at' => \Carbon\Carbon::now()
            ])]);

        // By default,
        // attach the user
        // to the prospect
        $prospect->users()->syncWithoutDetaching($user);
        $thread->users()->syncWithoutDetaching($user);
        // Send email notification
        UserProspectMessage::dispatchAfterResponse($message, $user);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Detach user from a given message
     */
    public function destroy(Project $project, Prospect $prospect, Thread $thread, Message $message, User $user)
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);

        // Detach the user
        // from the message
        $user->messages()->detach($message->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
