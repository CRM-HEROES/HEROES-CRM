<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\Prospect;
use App\Services\Docusign;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function generate(Project $project, Prospect $prospect, Order $order, Document $document)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->for == 'invoice', 404);

        if ($invoice = Invoice::where([
            'order_id' => $order->id,
            'document_id' => $document->id
        ])->first()) {
            $invoice->delete();
        }

        return Invoice::create([
            'order_id' => $order->id,
            'document_id' => $document->id,
            'creator_id' => auth()->id()
        ]);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function show(Project $project, Prospect $prospect, Order $order, Document $document)
    {
        $invoice = $this->generate($project, $prospect, $order, $document);

        /**
         * $invoice->path is generated in 
         * @see app\Observers\InvoiceObserver::createDocument
         */
        $file = Storage::disk("documents")->path($invoice->path);

        return response()
            ->file($file);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function sign(Project $project, Prospect $prospect, Order $order, Document $document, Docusign $docusign)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->for == 'invoice', 404);

        // Check if project is already associated
        // to a docusign account
        $docusign->setProject($project);
        $setting = ProjectSetting::get($project, 'docusign');
        if (
            !$setting || 
            !is_array($setting) || 
            !$setting['token'] || 
            !$setting['access_token']
        ) {
            return redirect()->route('project.docusign.connect', $project);
        }

        $invoice = Invoice::firstOrCreate([
            'order_id' => $order->id,
            'document_id' => $document->id
        ]);

        // Sign document
        $signer = auth()->user();
        $data = $docusign->signDocument(
			$setting['token']['access_token'],
			route('project.prospect.index', [$project]),
			// Document
			[
				'path' => Storage::disk("invoices")->path($invoice->path)
			],
			// Signer recipient model
			[
	        	'email' => $signer->email, 
	        	'name' => $signer->name,
	            'recipient_id' => "1", 
	            'routing_order' => "1",
	            'client_user_id' => $signer->id,
	        ]
		);

        // $document->docusign_envelope_id = $data['envelope_id'];
        // $document->state = "signing";
        // $document->signer_id = auth()->id();
        $document->save();

        return redirect()->to($data['url']);
    }
}
