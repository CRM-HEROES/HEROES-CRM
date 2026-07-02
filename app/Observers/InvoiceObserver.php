<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Utils\Document\InvoicePDFRenderer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->createDocument($invoice);
    }
    
    /**
     * Handle the Invoice "deleting" event.
     */
    public function deleting(Invoice $invoice): void
    {
        $this->removeDocument($invoice);
    }
    
    /**
     * Create invoice document
     */
    protected function createDocument(Invoice $invoice): void
    {
        $documentDisk = Storage::disk("documents");

        // Create document
        $pdfRenderer = new InvoicePDFRenderer($invoice);
        
        $outputFile = $pdfRenderer->render();

        $document = $invoice->document;
        if ($document->folder_id) {
            $folderDisk = Storage::disk('folders');
            
            $project = $document->project;
            $prospect = $invoice->order->prospect;

            // Copy generated document
            $pathinfo = pathinfo($outputFile);
            $path = $project->slug . "/" . $prospect->id . "/" . $document->folder_id . '/' . Str::random(30) . '.pdf';
            $stream = $documentDisk->readStream($project->slug . '/' . $pathinfo['basename']);
            $folderDisk->writeStream($path, $stream);

            $prospect
                ->files()
                ->create([
                    'folder_id' => $document->folder_id,
                    'from_user' => 1,
                    'creator_id' => auth()->id(),
                    'path' => $path,
                    'name' => $document->name,
                    'size' => filesize($outputFile)
                ]);
        }

        // Copy generated document
        $pathinfo = pathinfo($outputFile);
        $path = $invoice->document->project->slug . '/' . $pathinfo['basename'];
        $documentDisk->copy($pathinfo['basename'], $path);

        $invoice->update([
            'path' => $path,
            'size' => $documentDisk->size($path),
            'creator_id' => auth()->id()
        ]);
    }
    
    
    /**
     * Remove invoice document
     */
    protected function removeDocument(Invoice $invoice): void
    {
        $documentDisk = Storage::disk("documents");

        if ($invoice->path && $documentDisk->exists($invoice->path)) {
            $documentDisk->delete($invoice->path);
        }
    }
}
