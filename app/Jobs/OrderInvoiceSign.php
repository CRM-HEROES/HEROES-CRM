<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Docusign;
use App\Utils\ProjectSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceSign implements ShouldQueue
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
    public function handle(Docusign $docusign): void
    {
        // Check if project is already associated
        // to a docusign account
        $project = $this->document->project;
        
        $docusign->setProject($project);
        $setting = ProjectSetting::get($project, 'docusign');
        if (
            !$setting || 
            !is_array($setting) || 
            !$setting['token'] || 
            !$setting['access_token']
        ) {
            return false;
        }

        // Check if prospect have email
        if (
            !$this->order->prospect || !$this->order->prospect->email
        ) {
            return false;
        }

        // Generate invoice
        $invoice = Invoice::firstOrCreate([
            'order_id' => $this->order->id,
            'document_id' => $this->document->id
        ]);

        // Sign document
        $prospect = $this->order->prospect;
        $data = $docusign->signDocument(
			$setting['token']['access_token'],
			route('project.prospect.index', [$project]),
			// Document
			[
				'path' => Storage::disk("invoices")->path($invoice->path)
			],
			// Signer recipient model
			[
	        	'email' => $prospect->email, 
	        	'name' => $prospect->full_name,
	            'recipient_id' => "1", 
	            'routing_order' => "1",
	            'client_user_id' => $prospect->id,
	        ]
		);

        $invoice->docusign_envelope_id = $data['envelope_id'];
        $invoice->state = "signing";
        // $document->signer_id = auth()->id();
        $invoice->save();
    }
}
