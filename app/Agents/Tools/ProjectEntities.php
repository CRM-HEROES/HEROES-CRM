<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Calendar;
use App\Models\Category;
use App\Models\Document;
use App\Models\EventDescriptionTemplate;
use App\Models\Field;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Label;
use App\Models\MessageTemplate;
use App\Models\OrderAction;
use App\Models\OrderStatus;
use App\Models\OrderStep;
use App\Models\Pipeline;
use App\Models\Questionnaire;
use App\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Builder;

/**
 * The things a project is configured with that an agent may create, change or delete: ready-made texts, filters and their
 * categories, groups, pipelines, folders and custom fields. One description per kind: where its route is, what it holds,
 * which right each action needs. The tools that change them only differ by the action.
 *
 * Only plain, project-level configuration: nothing that carries users, rights or credentials (roles, channels, lines,
 * calendars shared with users, products and their prices are not here).
 */
class ProjectEntities
{
    /**
     * kind => route, label, model, fields (name => [type, max]), required on creation, the right of each action (null: the
     * CRM asks none for this kind), the other fixed values the route needs.
     */
    public const KINDS = [
        'sms_template' => [
            'route' => 'sms-template', 'label' => 'modèle de SMS', 'model' => SmsTemplate::class,
            'fields' => ['name' => ['string', 120], 'body' => ['string', 1000]], 'required' => ['name', 'body'],
            'rights' => ['create' => 'projectSmsTemplateAdd', 'update' => 'projectSmsTemplateUpdate', 'delete' => 'projectSmsTemplateDelete'],
        ],
        'message_template' => [
            'route' => 'message-template', 'label' => 'modèle de message (e-mail)', 'model' => MessageTemplate::class,
            'fields' => ['name' => ['string', 120], 'subject' => ['string', 255], 'body' => ['string', 20000]], 'required' => ['name', 'body'],
            'rights' => ['create' => null, 'update' => null, 'delete' => null],
        ],
        'event_description_template' => [
            'route' => 'event-description-template', 'label' => 'modèle de description de rendez-vous', 'model' => EventDescriptionTemplate::class,
            'fields' => ['name' => ['string', 120], 'body' => ['string', 5000]], 'required' => ['name', 'body'],
            'rights' => ['create' => null, 'update' => null, 'delete' => null],
        ],
        'category' => [
            'route' => 'category', 'label' => 'catégorie de filtres', 'model' => Category::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'color' => ['color'], 'bgcolor' => ['color'], 'unique' => ['bool']],
            'required' => ['name'], 'fixed' => ['for' => 'prospect'],
            'rights' => ['create' => 'projectCategoryAdd', 'update' => 'projectCategoryUpdate', 'delete' => 'projectCategoryDelete'],
        ],
        'label' => [
            'route' => 'category/{parent}/label', 'label' => 'filtre (label)', 'model' => Label::class, 'parent' => 'category',
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'color' => ['color'], 'bgcolor' => ['color']],
            'required' => ['name', 'color', 'bgcolor'],
            'rights' => ['create' => 'projectCategoryLabelAdd', 'update' => 'projectCategoryLabelUpdate', 'delete' => 'projectCategoryLabelDelete'],
        ],
        'group' => [
            'route' => 'group', 'label' => 'groupe', 'model' => Group::class,
            'fields' => ['name' => ['string', 120], 'color' => ['color'], 'bgcolor' => ['color']], 'required' => ['name'],
            'rights' => ['create' => 'projectGroupAdd', 'update' => 'projectGroupUpdate', 'delete' => 'projectGroupDelete'],
        ],
        'pipeline' => [
            'route' => 'pipeline', 'label' => 'pipeline', 'model' => Pipeline::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'color' => ['color'], 'bgcolor' => ['color']], 'required' => ['name'],
            'rights' => ['create' => 'projectPipelineAdd', 'update' => 'projectPipelineUpdate', 'delete' => 'projectPipelineDelete'],
        ],
        'document' => [
            'route' => 'document', 'label' => 'modèle de document (PDF)', 'model' => Document::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'for' => ['enum', ['prospect', 'order', 'invoice']], 'folder_id' => ['int']],
            'required' => ['name'],
            'rights' => ['create' => 'projectDocumentAdd', 'update' => 'projectDocumentUpdate', 'delete' => 'projectDocumentDelete'],
        ],
        'questionnaire' => [
            'route' => 'questionnaire', 'label' => 'questionnaire', 'model' => Questionnaire::class,
            'fields' => ['name' => ['string', 120]], 'required' => ['name'],
            'rights' => ['create' => 'projectQuestionnaireAdd', 'update' => 'projectQuestionnaireUpdate', 'delete' => 'projectQuestionnaireDelete'],
        ],
        'order_status' => [
            'route' => 'order-status', 'label' => 'état de devis', 'model' => OrderStatus::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'color' => ['color'], 'bgcolor' => ['color']], 'required' => ['name'],
            'rights' => ['create' => 'projectOrderStatusAdd', 'update' => 'projectOrderStatusUpdate', 'delete' => 'projectOrderStatusDelete'],
        ],
        'order_action' => [
            'route' => 'order-action', 'label' => 'action sur les devis', 'model' => OrderAction::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'color' => ['color'], 'bgcolor' => ['color']], 'required' => ['name'],
            'rights' => ['create' => 'projectOrderActionAdd', 'update' => 'projectOrderActionUpdate', 'delete' => 'projectOrderActionDelete'],
        ],
        'order_step' => [
            'route' => 'order-step', 'label' => 'étape de devis', 'model' => OrderStep::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'color' => ['color'], 'bgcolor' => ['color']], 'required' => ['name'],
            'rights' => ['create' => 'projectOrderStepAdd', 'update' => 'projectOrderStepUpdate', 'delete' => 'projectOrderStepDelete'],
        ],
        'calendar' => [
            'route' => 'calendar', 'label' => 'calendrier (type de rendez-vous)', 'model' => Calendar::class,
            'fields' => ['name' => ['string', 120], 'color' => ['color'], 'bgcolor' => ['color']], 'required' => ['name'],
            'rights' => ['create' => 'projectCalendarAdd', 'update' => 'projectCalendarUpdate', 'delete' => 'projectCalendarDelete'],
        ],
        'folder' => [
            'route' => 'folder', 'label' => 'dossier de prospect', 'model' => Folder::class,
            'fields' => ['name' => ['string', 120], 'private' => ['bool'], 'color' => ['color'], 'bgcolor' => ['color']],
            'required' => ['name'], 'fixed' => ['for' => 'prospect'],
            'rights' => ['create' => 'projectFolderAdd', 'update' => 'projectFolderUpdate', 'delete' => 'projectFolderDelete'],
        ],
        'field' => [
            'route' => 'field', 'label' => 'champ personnalisé de prospect', 'model' => Field::class,
            'fields' => ['name' => ['string', 120], 'description' => ['string', 500], 'default_value' => ['string', 255], 'required' => ['bool'], 'unique' => ['bool'], 'searchable' => ['bool']],
            'required' => ['name'], 'fixed' => ['for' => 'prospect', 'type' => 'text', 'meta' => true],
            'rights' => ['create' => 'projectFieldAdd', 'update' => 'projectFieldUpdate', 'delete' => 'projectFieldDelete'],
        ],
    ];

    public static function kinds(): array
    {
        return array_keys(self::KINDS);
    }

    /**
     * The union of the fields of every kind, for the schema of a tool.
     *
     * @return array<string, array>
     */
    public static function valueSchema(): array
    {
        $schema = [];

        foreach (self::KINDS as $kind) {
            foreach ($kind['fields'] as $name => $rule) {
                $schema[$name] = match ($rule[0]) {
                    'bool' => ['type' => 'boolean'],
                    'int' => ['type' => 'integer'],
                    'enum' => ['type' => 'string', 'enum' => $rule[1]],
                    'color' => ['type' => 'string', 'pattern' => '^#[0-9a-fA-F]{6}$', 'description' => 'Couleur « #RRGGBB ».'],
                    default => ['type' => 'string', 'maxLength' => max($rule[1], $schema[$name]['maxLength'] ?? 0)],
                };
            }
        }

        return $schema;
    }

    /**
     * The item of a kind in the project of the run (the usual rules of access of the CRM apply), or null.
     */
    public function find(AgentExecutionContext $context, string $kind, int $id, ?int $parent = null): mixed
    {
        $projectId = $context->project->id;

        $query = match ($kind) {
            'label' => Label::query()->whereHas('category', fn (Builder $c) => $c->where('project_id', $projectId)->when($parent, fn ($q) => $q->whereKey($parent))),
            'field' => Field::query()->where('project_id', $projectId)->where('for', 'prospect')->where('meta', true),
            default => (self::KINDS[$kind]['model'])::query()->where('project_id', $projectId),
        };

        return $id > 0 ? $query->find($id) : null;
    }

    /**
     * The values of an input checked against the fields of the kind: [values] or an error text.
     *
     * @return array|string
     */
    public function values(string $kind, array $input, bool $creating): array|string
    {
        $spec = self::KINDS[$kind];
        $values = [];

        foreach ((array) ($input['values'] ?? []) as $name => $value) {
            if (!isset($spec['fields'][$name])) {
                return "« $name » n'est pas un champ de ce type (" . $spec['label'] . ") : " . implode(', ', array_keys($spec['fields'])) . '.';
            }

            $rule = $spec['fields'][$name];
            if ($rule[0] === 'bool') {
                $values[$name] = (bool) $value;
            } elseif ($rule[0] === 'int') {
                if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
                    return "« $name » doit être un nombre entier.";
                }
                $values[$name] = (int) $value;
            } elseif ($rule[0] === 'enum') {
                if (!in_array($value, $rule[1], true)) {
                    return "« $name » doit valoir l'une de ces valeurs : " . implode(', ', $rule[1]) . '.';
                }
                $values[$name] = $value;
            } elseif ($rule[0] === 'color') {
                if (!is_string($value) || !preg_match('/\A#[0-9a-fA-F]{6}\z/', $value)) {
                    return "« $name » doit être une couleur « #RRGGBB ».";
                }
                $values[$name] = $value;
            } else {
                $text = is_string($value) ? $value : (string) json_encode($value);
                // Names and descriptions are plain text; the body of an e-mail template keeps its markup
                $values[$name] = $name === 'body' && $kind === 'message_template' ? $text : trim(strip_tags($text));
                if (mb_strlen($values[$name]) > $rule[1]) {
                    return "« $name » est trop long (" . $rule[1] . ' caractères au plus).';
                }
            }
        }

        if ($creating) {
            foreach ($spec['required'] as $name) {
                if (!isset($values[$name]) || $values[$name] === '') {
                    return "« $name » est obligatoire pour créer un(e) " . $spec['label'] . '.';
                }
            }
        } elseif (!$values) {
            return 'Rien à modifier : donne au moins une valeur.';
        }

        return $values;
    }
}
