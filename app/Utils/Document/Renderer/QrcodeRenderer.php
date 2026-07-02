<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrcodeRenderer implements FieldRenderer
{
    public function __construct()
    {}

    /**
     * Render a document field
     * 
     * @param  $content
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if ($field->type != "qrcode") {
            return $field->content;
        }

        $content = base64_encode(QrCode::format('png')->size(100)->generate($field->content));

        return "<img style=\"width: 100%;\" src=\"data:image/png;base64, {$content}\" />";
    }
}