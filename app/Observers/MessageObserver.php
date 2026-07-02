<?php

namespace App\Observers;

use App\Jobs\ProspectSendToThreadMessage;
use App\Jobs\ProspectSendToUserThreadMessage;
use App\Models\Message;
use \App\Jobs\ProspectThreadMessage;
use App\Utils\Field\Renderer\ProjectFieldRenderer;
use App\Utils\Field\Renderer\ProspectFieldRenderer;
use Illuminate\Support\Facades\Storage;

class MessageObserver
{
    /**
     * Handle the Message "creating" event.
     */
    public function creating(Message $message): void
    {
        $message->body = $this->computeMessage($message);
        $message->plain_text = $this->htmlToText($message->body);
    }
   
    /**
     * Handle the Message "creating" event.
     */
    public function created(Message $message): void
    {
        $this->sendProspectEmail($message);
        $this->sendToEmail($message);
        $this->sendToUser($message);
    }
   
    /**
     * Handle the Message "deleting" event.
     */
    public function deleting(Message $message): void
    {
        $this->removeAttachments($message);
    }
    
    /**
     * Send email to the prospect
     * if thread is not private
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    protected function sendProspectEmail(Message &$message)
    {
        if (!$message->from_user) {
            return;
        }

        $thread = $message->thread;
        if (!$thread) {
            return;
        }

        if ($thread->private) {
            return;
        }

        $prospect = $message->prospect;
        if (!$prospect) {
            return;
        }

        if (!$prospect->email) {
            $message->update([
                'error' => trans('email.error.prospect_without_email', ['prospect' => $prospect->full_name])
            ]);
            \ProjectLog::error($thread->project, trans('email.error.prospect_without_email', ['prospect' => $prospect->full_name]));
            return;
        }

        ProspectThreadMessage::dispatchAfterResponse($message);
    }
    
    /**
     * Send email to the associated email
     * 
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    protected function sendToEmail(Message &$message)
    {
        if (!$message->from_user) {
            return;
        }

        $thread = $message->thread;
        if (!$thread) {
            return;
        }

        if (!$thread->send_to) {
            return;
        }

        ProspectSendToThreadMessage::dispatchAfterResponse($message);
    }
    
    /**
     * Send email to the associated user
     * 
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    protected function sendToUser(Message &$message)
    {
        if (!$message->from_user) {
            return;
        }

        $thread = $message->thread;
        if (!$thread) {
            return;
        }

        if (!$thread->user_id) {
            return;
        }

        $message->users()->syncWithoutDetaching($thread->user_id);
        ProspectSendToUserThreadMessage::dispatchAfterResponse($message);
    }
    
    /**
     * Delete associated attachment
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    protected function removeAttachments(Message &$message)
    {
        foreach ($message->attachments as $attachment) {
            if (Storage::disk('messages')->exists($attachment->path)) {
                Storage::disk('messages')->delete($attachment->path);
            }
        }
    }
    
    /**
     * Compute mail message
     */
    protected function computeMessage(Message $message)
    {
        $renderers = [
            new ProspectFieldRenderer($message->thread->project, $message->prospect),
            new ProjectFieldRenderer($message->thread->project)
        ];

        return array_reduce($renderers, function($carry, $renderer) {
            return $renderer->render($carry);
        }, $message->body);
    }

    /**
     * 
     */
    protected function htmlToText($html) {
        // Remove HTML tags
        $text = strip_tags($html);
    
        // Convert HTML entities to plain text
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
        // Normalize line breaks
        $text = preg_replace('/\R+/u', "\n", $text);
    
        // Trim extra whitespace
        $text = trim($text);

        $text = strtolower($text);
    
        return $text;
    }
}
