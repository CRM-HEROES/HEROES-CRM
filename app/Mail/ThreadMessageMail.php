<?php

namespace App\Mail;

use App\Models\Message;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ThreadMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;

        if ($this->message->creator) {
            $this->replyTo($this->message->creator->email, $this->message->creator->name);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous avez un nouveau message',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return (new Content(
            view: 'emails.thread-message',
        ))->with([
            'icon' => $this->icon(),
            'title' => $this->message->thread->project->name,
            'threadMessage' => $this->message->body
        ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return $this->message->attachments->map(function($attachment) {
            return Storage::disk('messages')->path($attachment->path);
        })->toArray();
    }

    /**
     * 
     */
    protected function icon()
    {
        $thumbnail = (new LogoThumbnail($this->message->thread->project, 40))->generate();

        if ($thumbnail) {
            return "data:image/jpeg;base64," . base64_encode($thumbnail);
        } else {
            return "";
        }
    }
}
