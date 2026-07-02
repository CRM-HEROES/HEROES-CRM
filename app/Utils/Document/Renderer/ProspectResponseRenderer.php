<?php

namespace App\Utils\Document\Renderer;

use App\Utils\Document\FieldRenderer;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\Prospect;
use App\Models\QuestionnaireQuestion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProspectResponseRenderer implements FieldRenderer
{
    protected $types = ['text', 'html', 'qrcode', 'order-table'];
    protected Prospect $prospect;
    
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
        if (!in_array($field->type, $this->types)) {
            return $field->content;
        }

        $out = [];
        preg_match_all("/\\{prospect.question.(\\d+)\\}/", $field->content, $out, PREG_PATTERN_ORDER);

        if (empty($out)) {
            return $field->content;
        }

        return QuestionnaireQuestion
            ::whereIn('id', $out[1])
            ->whereHas('section.questionnaire', function($query) {
                $query->where('project_id', $this->prospect->project_id);
            })
            ->get()
            ->reduce(function ($carry, $question) {
                if ($question->type == 'image') {
                    $response = $this->prospect
                        ->responses()
                        ->where('question_id', $question->id)
                        ->first();
    
                    if ($response) {
                        $imageData = base64_encode(Storage::disk('questionnaires')->get($response->response));
                        $mime = Storage::disk('questionnaires')->mimeType($response->response);
                        $base64Image = 'data:' . $mime . ';base64,' . $imageData;

                        $response = "<img style=\"width: 100%;\" src=\"{$base64Image}\" />";
                    } else {
                        $response = "";
                    }
                } else {
                    $response = $this->prospect
                        ->responses()
                        ->where('question_id', $question->id)
                        ->get()
                        ->map(function($response) {
                            return $response->response;
                        })
                        ->join(', ');    
                }

                return Str::replace(
                    "{prospect.question.{$question->id}}", 
                    $response, 
                    $carry
                );
            }, $field->content);
    }
}