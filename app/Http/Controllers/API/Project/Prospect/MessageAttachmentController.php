<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Thread;
use App\Utils\Message\AttachmentThumbnail;
use Illuminate\Support\Facades\Storage;

class MessageAttachmentController extends Controller
{
    /**
     * Display attachments
     * attached to the message
     */
    public function show(
        Project $project, 
        Prospect $prospect, 
        Thread $thread, 
        Message $message, 
        MessageAttachment $attachment
    )
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);
        // Attachment associated to the message
        abort_unless($message->id == $attachment->message_id, 404);

        $disk = Storage::disk('messages');
        
        abort_unless($disk->exists($attachment->path), 404);

        return response($disk->get($attachment->path))
            ->header('Content-Type', $disk->mimeType($attachment->path))
            ->header('Content-Disposition', 'attachment; filename="' . $attachment->name . '"');
    }

    /**
     * Display attachment thumbnail
     * Need to install
     * - Imagick
     * - GhostScript
     * To enable to generate thumbnail
     */
    public function thumbnail(
        Project $project, 
        Prospect $prospect, 
        Thread $thread, 
        Message $message, 
        MessageAttachment $attachment
    )
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);
        // Attachment associated to the message
        abort_unless($message->id == $attachment->message_id, 404);

        $thumbnail = (new AttachmentThumbnail($attachment))->generate();

        if (!$thumbnail) {
            return response('Cannot generate thumbnail.', 400);
        }

        // Show thumbnail
        return response($thumbnail)->withHeaders([
            'Content-Type' => "image/jpeg",
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Project $project, 
        Prospect $prospect, 
        Thread $thread, 
        Message $message, 
        MessageAttachment $attachment
    )
    {
        // Thread associated to the current project
        abort_unless($project->id == $thread->project_id, 404);
        // Prospect associated to the message
        abort_unless($prospect->id == $message->prospect_id, 404);
        // Thread associated to the message
        abort_unless($thread->id == $message->thread_id, 404);
        // Attachment associated to the message
        abort_unless($message->id == $attachment->message_id, 404);

        $message->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
