<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Questionnaire;

/**
 * The questionnaires of the project and how they are built: sections, questions, the choices of a question. What a prospect
 * answered is not here (questionnaire_responses). A questionnaire the user is not given is not listed (the usual rule of the CRM).
 */
class QuestionnaireOverviewTool implements AgentToolInterface
{
    public function key(): string
    {
        return 'questionnaire_overview';
    }

    public function description(): string
    {
        return "Questionnaires du projet. Sans questionnaire_id : la liste (nom, nombre de sections et de questions). Avec questionnaire_id : sa structure, section par section, avec chaque question (nom, type : radio, checkbox, text, textarea, date, image, file) et ses choix. "
            . "Ne donne aucune réponse de prospect (questionnaire_responses).";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['questionnaire_id' => ['type' => 'integer']],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'questionnaire';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $query = Questionnaire::query()->where('project_id', $context->project->id);

        if (empty($input['questionnaire_id'])) {
            $questionnaires = $query->withCount('sections')->orderBy('name')->limit(50)->get();

            return ToolResult::ok(
                ['questionnaires' => $questionnaires->map(fn ($q) => ['id' => $q->id, 'name' => $this->text($q->name), 'sections' => (int) $q->sections_count])->all()],
                'Questionnaires du projet',
                $questionnaires->count()
            );
        }

        $questionnaire = $query->with(['sections' => fn ($s) => $s->orderBy('order')->with(['questions' => fn ($q) => $q->orderBy('order')->with(['options' => fn ($o) => $o->orderBy('order')])])])->find((int) $input['questionnaire_id']);

        if (!$questionnaire) {
            return ToolResult::error("Questionnaire {$input['questionnaire_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        return ToolResult::ok(
            [
                'id' => $questionnaire->id,
                'name' => $this->text($questionnaire->name),
                'sections' => $questionnaire->sections->map(fn ($section) => [
                    'id' => $section->id,
                    'name' => $this->text($section->name),
                    'questions' => $section->questions->map(fn ($question) => [
                        'id' => $question->id,
                        'name' => $this->text($question->name),
                        'type' => $question->type,
                        'choices' => $question->options->map(fn ($option) => ['id' => $option->id, 'name' => $this->text($option->name)])->all(),
                    ])->all(),
                ])->all(),
            ],
            "Questionnaire {$questionnaire->id}",
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function text(mixed $value): string
    {
        return mb_substr(strip_tags((string) $value), 0, 200);
    }
}
