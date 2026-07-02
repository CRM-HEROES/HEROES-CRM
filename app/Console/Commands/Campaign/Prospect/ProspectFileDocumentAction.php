<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Jobs\ProspectDocumentToFolder;
use App\Models\Document;
use App\Models\Folder;
use App\Utils\Document\ProspectPDFRenderer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Add a generated document
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
class ProspectFileDocumentAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->action->value ||
            // !isset($this->action->value['source']) || !$this->action->value['source'] ||
            !isset($this->action->value['folder']) || !$this->action->value['folder'] ||
            !isset($this->action->value['document']) || !$this->action->value['document']) {
            return null;
        }

        // 
        $document = Document
            ::where('id', $this->action->value['document'])
            ->where('project_id', $this->model->project_id)
            ->first(['id']);
    
        if (!$document) {
            return null;
        }

        // In which message folder
        // should we send the invoice
        $folder = Folder
            ::where('id', $this->action->value['folder'])
            ->where('project_id', $this->model->project_id)
            ->first(['id']);
    
        if (!$folder) {
            return null;
        }

    ProspectDocumentToFolder::dispatch($this->model, $document, $folder, 'file'/*$this->action->value['source']*/);
    }
}