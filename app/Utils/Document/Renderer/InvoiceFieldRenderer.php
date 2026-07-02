<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Invoice;
use App\Models\Project;
use App\Utils\Field\FieldRenderer as FieldFieldRenderer;
use App\Utils\Field\Renderer\InvoiceFieldRenderer as RendererInvoiceFieldRenderer;

class InvoiceFieldRenderer implements FieldRenderer
{
    protected $productFields;
    protected $orderFields;
    protected $types = ['text', 'html', 'order-table'];
    protected Invoice $invoice;
    protected FieldFieldRenderer $fieldRenderer; 
    
    public function __construct(Project $project, Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->fieldRenderer = new RendererInvoiceFieldRenderer($project, $invoice);
    }

    /**
     * Render a document field
     * 
     * @param  field
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if (!in_array($field->type, $this->types)) {
            return $field->content;
        }

        $content = $this->getInvoiceField($field, $page);

        return $content;
    }

    /**
     * Render a document field
     * 
     * @param  field
     */
    public function getInvoiceField($field, $page)
    {
        return $this->fieldRenderer->render($field->content, $page);
    }
}