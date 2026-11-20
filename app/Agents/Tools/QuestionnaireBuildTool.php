<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\Questionnaire;

/**
 * Builds a questionnaire in one go: its sections, their questions and the choices of the questions that have some. Creates a new
 * questionnaire, or adds sections to an existing one. Through the routes of the CRM, one call per piece.
 *
 * The routes of the questionnaires check no right: the tool asks the ones of the screen (add, update). Limits: 10 sections, 20
 * questions a section (40 in all), 12 choices a question. If the CRM refuses a piece half way, what was made stays and is told
 * (with the id of the questionnaire), never silently left.
 */
class QuestionnaireBuildTool implements AgentToolInterface, ChecksBeforeApproval
{
    public const TYPES = ['radio', 'checkbox', 'text', 'textarea', 'date', 'image', 'file'];
    public const WITH_CHOICES = ['radio', 'checkbox'];
    public const MAX_SECTIONS = 10;
    public const MAX_QUESTIONS_PER_SECTION = 20;
    public const MAX_QUESTIONS = 40;
    public const MAX_CHOICES = 12;

    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'questionnaire_build';
    }

    public function description(): string
    {
        return "Construit un questionnaire : sections, questions (type : radio ou checkbox avec leurs choix, text, textarea, date, image, file) en une seule demande. "
            . "Sans questionnaire_id : crée le questionnaire (name obligatoire) ; avec questionnaire_id : ajoute ces sections à un questionnaire existant. "
            . self::MAX_SECTIONS . " sections, " . self::MAX_QUESTIONS_PER_SECTION . " questions par section, " . self::MAX_CHOICES . " choix par question au plus. "
            . "Pour renommer ou supprimer un questionnaire : project_entity_save / project_entity_delete (entity = questionnaire). Les questionnaires existants se lisent avec questionnaire_overview.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'questionnaire_id' => ['type' => 'integer', 'description' => 'Ajouter à ce questionnaire au lieu d\'en créer un.'],
                'name' => ['type' => 'string', 'maxLength' => 120, 'description' => 'Nom du nouveau questionnaire.'],
                'sections' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => self::MAX_SECTIONS,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'maxLength' => 120],
                            'questions' => [
                                'type' => 'array',
                                'maxItems' => self::MAX_QUESTIONS_PER_SECTION,
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string', 'maxLength' => 255],
                                        'type' => ['type' => 'string', 'enum' => self::TYPES],
                                        'choices' => ['type' => 'array', 'maxItems' => self::MAX_CHOICES, 'items' => ['type' => 'string', 'maxLength' => 255], 'description' => 'Pour radio et checkbox seulement.'],
                                    ],
                                    'required' => ['name', 'type'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'required' => ['name', 'questions'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['sections'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'questionnaire';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $plan = $this->plan($context, $input);

        return $plan instanceof ToolResult ? $plan : null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $plan = $this->plan($context, $input);

        if ($plan instanceof ToolResult) {
            return $plan;
        }

        $questionnaireId = $plan['questionnaire_id'];
        $made = ['sections' => 0, 'questions' => 0, 'choices' => 0];

        $stop = function (string $what, $response) use (&$questionnaireId, &$made) {
            $error = $response->toError();

            return ToolResult::error(
                "Le CRM a refusé : $what (" . ($error->data['error'] ?? 'refusé') . ')'
                . ($questionnaireId ? " Ce qui était déjà fait reste : questionnaire {$questionnaireId}, {$made['sections']} section(s), {$made['questions']} question(s), {$made['choices']} choix. Corrige ou supprime-le avant de recommencer." : ''),
                $error->code
            );
        };

        if (!$questionnaireId) {
            $response = $this->api->call($context, 'POST', 'questionnaire', ['name' => $plan['name']]);
            if (!$response->ok() || empty($response->json['id'])) {
                return $response->ok() ? ToolResult::error('Le CRM n\'a pas donné l\'identifiant du questionnaire créé.', ToolResult::INTERNAL_ERROR) : $stop('le questionnaire', $response);
            }
            $questionnaireId = (int) $response->json['id'];
        }

        foreach ($plan['sections'] as $section) {
            $response = $this->api->call($context, 'POST', "questionnaire/$questionnaireId/section", ['name' => $section['name']]);
            if (!$response->ok() || empty($response->json['id'])) {
                return $response->ok() ? ToolResult::error('Le CRM n\'a pas donné l\'identifiant de la section créée.', ToolResult::INTERNAL_ERROR) : $stop("la section « {$section['name']} »", $response);
            }
            $sectionId = (int) $response->json['id'];
            $made['sections']++;

            foreach ($section['questions'] as $question) {
                $response = $this->api->call($context, 'POST', "questionnaire/$questionnaireId/section/$sectionId/question", ['name' => $question['name'], 'type' => $question['type']]);
                if (!$response->ok() || empty($response->json['id'])) {
                    return $response->ok() ? ToolResult::error('Le CRM n\'a pas donné l\'identifiant de la question créée.', ToolResult::INTERNAL_ERROR) : $stop("la question « {$question['name']} »", $response);
                }
                $questionId = (int) $response->json['id'];
                $made['questions']++;

                foreach ($question['choices'] as $choice) {
                    $response = $this->api->call($context, 'POST', "questionnaire/$questionnaireId/section/$sectionId/question/$questionId/option", ['name' => $choice]);
                    if (!$response->ok()) {
                        return $stop("le choix « $choice »", $response);
                    }
                    $made['choices']++;
                }
            }
        }

        return ToolResult::ok(
            ['done' => true, 'questionnaire_id' => $questionnaireId, 'created_questionnaire' => $plan['questionnaire_id'] === null] + $made,
            "Questionnaire $questionnaireId : {$made['sections']} section(s), {$made['questions']} question(s)",
            $made['questions']
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $sections = (array) ($input['sections'] ?? []);

        return [
            'action' => empty($input['questionnaire_id']) ? 'Créer un questionnaire' : 'Ajouter des sections au questionnaire ' . (int) $input['questionnaire_id'],
            'nom' => isset($input['name']) ? mb_substr(strip_tags((string) $input['name']), 0, 120) : null,
            'sections' => array_map(fn ($s) => mb_substr(strip_tags((string) ($s['name'] ?? '')), 0, 80) . ' (' . count((array) ($s['questions'] ?? [])) . ' question(s))', $sections),
        ];
    }

    /**
     * What will be created, cleaned and checked beforehand, or the refusal.
     *
     * @return ToolResult|array{questionnaire_id: ?int, name: ?string, sections: array}
     */
    protected function plan(AgentExecutionContext $context, array $input): ToolResult|array
    {
        $existing = (int) ($input['questionnaire_id'] ?? 0);
        $right = $existing ? 'projectQuestionnaireUpdate' : 'projectQuestionnaireAdd';

        if (!$context->owner->can($right, $context->project)) {
            return ToolResult::error("L'utilisateur propriétaire de l'agent n'a pas le droit de " . ($existing ? 'modifier' : 'créer') . ' un questionnaire dans ce projet.', ToolResult::PERMISSION_DENIED);
        }

        $name = null;
        if ($existing) {
            if (!Questionnaire::query()->where('project_id', $context->project->id)->whereKey($existing)->exists()) {
                return ToolResult::error("Questionnaire $existing introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
            }
        } else {
            $name = trim(strip_tags((string) ($input['name'] ?? '')));
            if ($name === '') {
                return ToolResult::error('Le nom du questionnaire est obligatoire pour en créer un.', ToolResult::VALIDATION_FAILED);
            }
        }

        $sections = [];
        $total = 0;

        foreach ((array) ($input['sections'] ?? []) as $i => $section) {
            $sectionName = trim(strip_tags((string) ($section['name'] ?? '')));
            if ($sectionName === '') {
                return ToolResult::error('La section ' . ($i + 1) . ' n\'a pas de nom.', ToolResult::VALIDATION_FAILED);
            }

            $questions = [];
            foreach ((array) ($section['questions'] ?? []) as $j => $question) {
                $label = "section « $sectionName », question " . ($j + 1);
                $questionName = trim(strip_tags((string) ($question['name'] ?? '')));
                $type = $question['type'] ?? null;
                $choices = array_values(array_filter(array_map(fn ($c) => trim(strip_tags((string) $c)), (array) ($question['choices'] ?? [])), fn ($c) => $c !== ''));

                if ($questionName === '' || !in_array($type, self::TYPES, true)) {
                    return ToolResult::error("$label : un nom et un type (" . implode(', ', self::TYPES) . ') sont obligatoires.', ToolResult::VALIDATION_FAILED);
                }
                if (in_array($type, self::WITH_CHOICES, true) && count($choices) < 2) {
                    return ToolResult::error("$label : une question « $type » a besoin d'au moins deux choix.", ToolResult::VALIDATION_FAILED);
                }
                if (!in_array($type, self::WITH_CHOICES, true) && $choices) {
                    return ToolResult::error("$label : seules les questions radio et checkbox ont des choix.", ToolResult::VALIDATION_FAILED);
                }

                $questions[] = ['name' => $questionName, 'type' => $type, 'choices' => $choices];
            }

            $total += count($questions);
            $sections[] = ['name' => $sectionName, 'questions' => $questions];
        }

        if (!$sections) {
            return ToolResult::error('Il faut au moins une section.', ToolResult::VALIDATION_FAILED);
        }
        if (count($sections) > self::MAX_SECTIONS || $total > self::MAX_QUESTIONS) {
            return ToolResult::error('Au plus ' . self::MAX_SECTIONS . ' sections et ' . self::MAX_QUESTIONS . ' questions à la fois.', ToolResult::LIMIT_EXCEEDED);
        }

        return ['questionnaire_id' => $existing ?: null, 'name' => $name, 'sections' => $sections];
    }
}
