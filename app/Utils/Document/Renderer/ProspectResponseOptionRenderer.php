<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Prospect;

class ProspectResponseOptionRenderer implements FieldRenderer
{
    protected Prospect $prospect;
    protected $checkbox = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAFMN540AAAACXBIWXMAAAsSAAALEgHS3X78AAABbElEQVRIieVUbXHDMAx96uV/TWEMWgaDUAiDMAYrhI1BIQxCIRRKGGjnTM5UR/5InDY/pjtdnETS07M+iJmhZadfiKiHWNwAf2RMXNIxBv+kBRFdkzFi2cUfiMgR0eilQVl+OGamaoiswQS/+DMkZ2XufxyHF8Fkefrr6v3ZaycOxxBK00GwAvAdzkGzVIoXMZtqEumvFPXORHTOEk6pNxG9xDZdhBBS+pI7/5igaYmjAXhXaJ+5rJpu+3ml2sZZ13qUUp3lTpyqwGH8XulsNkqNYy+O45AGzXL2PAHs8UvPp34vRmoux9NMO2xptU+KLZrixyme2d7WtUxOU6pJlMNLtms2HYwm5BZpyrpFukcFJqIDgFdp9vPEoGaY56iAcaSn7FCsAHo1QN+K07gyy1vYKQ8BnsOyCCzBgppZL2FZAxwHvBiJFRtoCbAztu+wui2Wi0pUqN/JAFrMcnZz+ateg6XW/7ertwEG8AOgGSRrc3cbDwAAAABJRU5ErkJggg==";
    
    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * Render a document field
     * 
     * @param  field
     */
    public function render(DocumentField $field, DocumentPage $page)
    {
        if ($field->type != 'prospect-option') {
            return $field->content;
        }

        if (!$this->prospect->responses()->where('option_id', $field->content)->first()) {
            return '';
        }

        return "<img style=\"width: 100%;\" src=\"{$this->checkbox}\" />";
    }
}