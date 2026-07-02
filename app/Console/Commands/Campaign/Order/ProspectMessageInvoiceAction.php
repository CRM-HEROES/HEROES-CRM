<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Jobs\OrderInvoiceToThread;
use App\Models\Document;
use App\Models\Thread;

/**
 * Send the generated invoice 
 * to a thread associated to
 * the prospect associated to the order
 * 
 * Data format:
 * {
 *    document: {document ID},
 *    thread: {thread ID, thread in which we send the message},
 *    body: {body, body of the message},
 * }
 */
class ProspectMessageInvoiceAction extends Action
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

        // Invoice Document Template
        $document = Document
            ::where('id', $this->action->value['document'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
            
        if (!$document) {
            return null;
        }

        // In which message thread
        // should we send the invoice
        $thread = Thread
            ::where('id', $this->action->value['thread'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first(['id']);
    
        if (!$thread) {
            return null;
        }

        OrderInvoiceToThread::dispatch($this->model, $document, $thread, $this->action->value['body'])->onQueue('documents');
    }
}