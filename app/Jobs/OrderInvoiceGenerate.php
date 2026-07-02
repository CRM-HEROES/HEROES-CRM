<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceGenerate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;
    protected Document $document;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, Document $document)
    {
        $this->order = $order;
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Invoice::firstOrCreate([
            'order_id' => $this->order->id,
            'document_id' => $this->document->id
        ]);
    }
}
