<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Folder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceToFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    protected Document $document;
    protected Folder $folder;
    protected $source;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, Document $document, Folder $folder, $source)
    {
        $this->order = $order;
        $this->document = $document;
        $this->folder = $folder;
        $this->source = $source;
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

        $this->createFileFromInvoice($this->order->prospect, $this->folder, $invoice, $this->source);
    }
    
    /**
     * Create prospect file from order invoice
     */
    protected function createFileFromInvoice($prospect, $folder, $invoice, $source)
    {
        $path = $prospect->project->slug . "/" . $prospect->id . "/" . $folder->id . "/" . pathinfo($invoice->path)['basename'];

        // copy the generated invoice
        // to attachment disk
        $documentDisk = Storage::disk('documents');
        $folderDisk = Storage::disk('folders');
        $folderDisk->writeStream($path, $documentDisk->readStream($invoice->path));

        // Create attachment object
        return $prospect
            ->files()
            ->create([
                'source' => $source,
                'folder_id' => $folder->id,
                'from_user' => 1,
                'path' => $path,
                'name' => $invoice->document->name . " - " . $invoice->order->id . ".pdf",
                'size' => $invoice->size,
            ]);
    }
}
