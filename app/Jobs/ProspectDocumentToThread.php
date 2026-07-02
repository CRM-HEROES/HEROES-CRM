<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Prospect;
use App\Models\Thread;
use App\Utils\Document\ProspectPDFRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProspectDocumentToThread implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Prospect $prospect;
    protected Document $document;
    protected Thread $thread;
    protected $body;

    /**
     * Create a new job instance.
     */
    public function __construct(Prospect $prospect, Document $document, Thread $thread, $body)
    {
        $this->prospect = $prospect;
        $this->document = $document;
        $this->thread = $thread;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create message
        $message = $this->createMessage();
        
        // Create message attachment from the order invoice
        $attachment = $this->createAttachmentFromDocument();
        // and attach the attachment to the message
        $message->attachments()->create($attachment);
    }
    
    /**
     * Create message to send to a thread
     */
    protected function createMessage()
    {
        // Create the message to send
        // to the thread
        return $this->prospect
            ->messages()
            ->create([
                'body' => $this->body,
                'thread_id' => $this->thread->id,
                'from_user' => 1
            ]);
    }

    /**
     * 
     */
    protected function createAttachmentFromDocument()
    {
        $pdfRenderer = (new ProspectPDFRenderer($this->document, $this->prospect));
        $outputFile = $pdfRenderer->render();
        
        // copy the generated document
        // to attachment disk
        $pathinfo = pathinfo($outputFile);
        $path = $this->prospect->project->slug . '/' . $pathinfo['basename'];

        $documentDisk = Storage::disk('documents');
        $messageDisk = Storage::disk('messages');
        $messageDisk->writeStream($path, $documentDisk->readStream($pathinfo['basename']));
        $documentDisk->delete($pathinfo['basename']);
        
        return [
            'path' => $path,
            'name' => $this->document->name . ".pdf",
            'size' => $messageDisk->size($path),
        ];
    }
}
