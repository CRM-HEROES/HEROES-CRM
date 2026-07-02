<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Action;
use App\Jobs\OrderInvoiceSign;
use App\Models\Document;
use App\Models\Order;

/**
 * Generate order invoice
 * 
 * Data format:
 * {
 *    document: {document ID, from which we generate the invoice}
 * }
 */
class OrderSignInvoiceAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (
            !$this->model instanceof Order ||
            !$this->action->value ||
            !isset($this->action->value['document']) || !$this->action->value['document']
        ) {
            return null;
        }

        // Invoice Document Template
        $document = Document
            ::where('id', $this->action->value['document'])
            ->where('project_id', $this->model->prospect->project_id)
            ->first();
            
        if (!$document) {
            return null;
        }

        // Generate order invoice 
        // using $document as template 
        OrderInvoiceSign::dispatch($this->model, $document)->onQueue('documents');
    }
}