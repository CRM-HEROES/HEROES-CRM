<?php

namespace App\Utils\Document;

use App\Models\Document;
use App\Models\Order;
use App\Models\Prospect;
use App\Utils\Document\Renderer\DateRenderer;
use App\Utils\Document\Renderer\FieldNameRenderer;
use App\Utils\Document\Renderer\ImageRenderer;
use App\Utils\Document\Renderer\MapRenderer;
use App\Utils\Document\Renderer\PageNumberRenderer;
use App\Utils\Document\Renderer\ProjectFieldRenderer;
use App\Utils\Document\Renderer\ProspectFieldRenderer;
use App\Utils\Document\Renderer\ProspectCategoryLabelRenderer;
use App\Utils\Document\Renderer\ProspectCategoryRenderer;
use App\Utils\Document\Renderer\ProspectResponseOptionRenderer;
use App\Utils\Document\Renderer\ProspectResponseRenderer;
use App\Utils\Document\Renderer\QrcodeRenderer;
use App\Utils\Document\Renderer\TextFieldRenderer;
use App\Utils\Document\Renderer\UserFieldRenderer;

class ProspectPDFRenderer extends PDFRenderer
{
    public function __construct(Document $document, Prospect $prospect, $pages = [])
    {
        parent::__construct($document, $pages);

        $project = $document->project;

        $this
            ->addRenderer(new DateRenderer($project))
            ->addRenderer(new ProjectFieldRenderer($project))
            ->addRenderer(new ProspectFieldRenderer($project, $prospect))
            ->addRenderer(new ProspectCategoryLabelRenderer($prospect))
            ->addRenderer(new ProspectCategoryRenderer($project, $prospect))
            ->addRenderer(new ProspectResponseOptionRenderer($prospect))
            ->addRenderer(new ProspectResponseRenderer($prospect))
            ->addRenderer(new UserFieldRenderer($project, auth()->user()))
            ->addRenderer(new FieldNameRenderer($project))
            ->addRenderer(new PageNumberRenderer())
            ->addRenderer(new ImageRenderer())
            ->addRenderer(new TextFieldRenderer())
            ->addRenderer(new QrcodeRenderer())
            ->addRenderer(new MapRenderer($project, $prospect, auth()->user()));
    }
}