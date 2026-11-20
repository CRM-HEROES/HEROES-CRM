<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Models\ProspectQuestionnaireResponse;
use App\Models\Questionnaire;

/**
 * What a prospect answered to a questionnaire. Personal data: only for a prospect the owner of the agent may see, and a file
 * or an image is never given (only that there is one). Each answer is cut.
 */
class QuestionnaireResponsesTool implements AgentToolInterface
{
    public function __construct(protected ReadGateway $reader)
    {
    }

    public function key(): string
    {
        return 'questionnaire_responses';
    }

    public function description(): string
    {
        return "Réponses d'un prospect à un questionnaire (questionnaire_overview donne les questionnaires) : pour chaque question, ce qui a été répondu (choix ou texte, tronqué). Un fichier ou une image n'est jamais donné, seulement sa présence. "
            . "Réservé aux prospects que l'utilisateur peut voir.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'prospect_id' => ['type' => 'integer'],
                'questionnaire_id' => ['type' => 'integer'],
            ],
            'required' => ['prospect_id', 'questionnaire_id'],
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
        $visible = $this->reader->visibleProspectIds($context, [(int) $input['prospect_id']], false);
        if (!$visible['ids']) {
            return ToolResult::error("Prospect {$input['prospect_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        $questionnaire = Questionnaire::query()->where('project_id', $context->project->id)->find((int) $input['questionnaire_id']);
        if (!$questionnaire) {
            return ToolResult::error("Questionnaire {$input['questionnaire_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        $responses = ProspectQuestionnaireResponse::query()
            ->where('prospect_id', $visible['ids'][0])
            ->whereHas('question.section', fn ($section) => $section->where('questionnaire_id', $questionnaire->id))
            ->with(['question:id,name,type', 'option:id,name'])
            ->limit(200)
            ->get();

        return ToolResult::ok(
            [
                'prospect_id' => $visible['ids'][0],
                'questionnaire' => mb_substr(strip_tags((string) $questionnaire->name), 0, 200),
                'answers' => $responses->map(fn ($response) => [
                    'question' => mb_substr(strip_tags((string) $response->question?->name), 0, 200),
                    'type' => $response->question?->type,
                    'answer' => in_array($response->question?->type, ['image', 'file'], true)
                        ? '[fichier joint]'
                        : mb_substr(strip_tags((string) ($response->option?->name ?? $response->response)), 0, 300),
                ])->all(),
            ],
            "Réponses du prospect {$visible['ids'][0]}",
            $responses->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
