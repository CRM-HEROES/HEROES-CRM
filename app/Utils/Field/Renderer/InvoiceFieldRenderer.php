<?php

namespace App\Utils\Field\Renderer;

use App\Models\Invoice;
use App\Utils\Field\FieldRenderer;
use App\Models\Project;
use App\Models\Order;
use Illuminate\Support\Str;

class InvoiceFieldRenderer implements FieldRenderer
{
    protected Project $project;
    protected Invoice $invoice;
    protected $fields;
    protected $defaultPattern = "/\\{invoice.(\\w+)\\}/";
    protected $defaultMatch = [];
    
    public function __construct(Project $project, Invoice $invoice)
    {
        $this->project = $project;
        $this->invoice = $invoice;
        $this->fields = collect([['slug' => "id"]]);
    }

    /**
     * Compute a content
     * 
     * @param  $content
     */
    public function render($content)
    {
        if (
            !preg_match_all($this->defaultPattern, $content, $this->defaultMatch)
        ) {
            return $content;
        }

        $content = Str::replace("{invoice.id}", $this->invoice->id, $content);
        $content = Str::replace("{invoice.date}", (new \Carbon\Carbon($this->invoice->created_at))->format("d/m/Y"), $content);

        return $content;
    }
}