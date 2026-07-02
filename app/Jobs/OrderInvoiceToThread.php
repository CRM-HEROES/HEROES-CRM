<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Thread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceToThread implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    protected Document $document;
    protected Thread $thread;
    protected $body;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, Document $document, Thread $thread, $body)
    {
        $this->order = $order;
        $this->document = $document;
        $this->thread = $thread;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate invoice
        $invoice = Invoice::firstOrCreate([
            'order_id' => $this->order->id,
            'document_id' => $this->document->id
        ]);

        // Create message
        $message = $this->createMessage();
        
        // Create message attachment from the order invoice
        $attachment = $this->createAttachmentFromInvoice($invoice);
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
        return $this->order->prospect
            ->messages()
            ->create([
                'body' => $this->body,
                'thread_id' => $this->thread->id,
                'from_user' => 1
            ]);
    }

    /**
     * Create message attachment from order invoice
     */
    protected function createAttachmentFromInvoice($invoice)
    {
        // copy the generated invoice
        // to attachment disk
        $documentDisk = Storage::disk('documents');
        $messageDisk = Storage::disk('messages');
        $messageDisk->writeStream($invoice->path, $documentDisk->readStream($invoice->path));

        // Create attachment object
        return [
            'path' => $invoice->path,
            'name' => $invoice->document->name . " - " . $invoice->order->id . ".pdf",
            'size' => $invoice->size,
        ];
    }
}
