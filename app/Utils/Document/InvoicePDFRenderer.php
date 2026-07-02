<?php

namespace App\Utils\Document;

use App\Models\Invoice;
use App\Utils\Document\Renderer\InvoiceFieldRenderer;

class InvoicePDFRenderer extends OrderPDFRenderer
{
    public function __construct(Invoice $invoice)
    {
        parent::__construct($invoice->document, $invoice->order);

        $this->addRenderer(new InvoiceFieldRenderer($invoice->document->project, $invoice));
    }
}