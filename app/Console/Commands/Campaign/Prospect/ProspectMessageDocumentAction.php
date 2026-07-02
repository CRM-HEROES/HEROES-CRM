<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Jobs\ProspectDocumentToThread;
use App\Models\Document;
use App\Models\Thread;

/**
 * Send document
 * to a thread associated to
 * the prospect
 * 
 * Data format:
 * {
 *    document: {document ID},
 *    thread: {thread ID, thread in which we send the message},
 *    body: {body, body of the message},
 * }
 */
class ProspectMessageDocumentAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->action->value ||
            !isset($this->action->value['body']) || !$this->action->value['body'] ||
            !isset($this->action->value['thread']) || !$this->action->value['thread'] ||
            !isset($this->action->value['document']) || !$this->action->value['document']) {
            return null;
        }

        // In which message thread
        // should we send the invoice
        $thread = Thread
            ::where('id', $this->action->value['thread'])
            ->where('project_id', $this->model->project_id)
            ->first();
    
        if (!$thread) {
            return null;
        }

        // 
        $document = Document
            ::where('id', $this->action->value['document'])
            ->where('project_id', $this->model->project_id)
            ->first();
    
        if (!$document) {
            return null;
        }

        ProspectDocumentToThread::dispatch($this->model, $document, $thread, $this->action->value['body']);
    }
}