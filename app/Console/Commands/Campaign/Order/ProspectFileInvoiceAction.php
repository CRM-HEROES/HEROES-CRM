<?php

namespace App\Console\Commands\Campaign\Order;

use App\Jobs\OrderInvoiceToFolder;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;

/**
 * Add the generated invoice
 * in a folder that belongs 
 * to the prospect associated to the order
 * 
 * Data format:
 * {
 *    document: {document ID, from which we generate the invoice},
 *    folder: {folder ID},
 *    source: {source, may be 'local' or 'google-drive'},
 * }
 */
class ProspectFileInvoiceAction extends OrderGenerateInvoiceAction
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->action->value ||
            !isset($this->action->value['source']) || !$this->action->value['source'] ||
            !isset($this->action->value['folder']) || !$this->action->value['folder'] ||
            !isset($this->action->value['document']) || !$this->action->value['document']) {
            return null;
        }

        // Invoice Document Template
        $document = Document
            ::where('id', $this->action->value['document'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
            
        if (!$document) {
            return null;
        }

        // In which message folder
        // should we send the invoice
        $folder = Folder
            ::where('id', $this->action->value['folder'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
    
        if (!$folder) {
            return null;
        }

        OrderInvoiceToFolder::dispatch($this->model, $document, $folder, $this->action->value['source'])->onQueue('documents');
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