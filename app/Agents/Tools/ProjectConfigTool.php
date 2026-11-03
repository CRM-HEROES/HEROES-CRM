<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Calendar;
use App\Models\Category;
use App\Models\Field;
use App\Models\Group;
use App\Models\OrderStatus;
use App\Workflows\Providers\ProviderRegistry;

/**
 * How the project is organised: the labels (statuses) by category, the groups,
 * the calendars, the custom fields, the order statuses and the messaging
 * channels. It gives the identifiers the other tools need ("NRP" is a label,
 * its id comes from here) and what the project calls things.
 *
 * Read through the models, so the owner only sees the categories, labels,
 * groups and calendars the CRM shows them.
 */
class ProjectConfigTool implements AgentToolInterface
{
    public const SECTIONS = ['labels', 'groups', 'calendars', 'fields', 'order_statuses', 'threads', 'messaging'];

    public function __construct(protected ProviderRegistry $providers)
    {
    }

    public function key(): string
    {
        return 'project_config';
    }

    public function description(): string
    {
        return "Décrit l'organisation du projet et donne les identifiants à utiliser avec les autres outils : "
            . "labels (les statuts, comme NRP) rangés par catégorie, groupes, calendriers, champs personnalisés des prospects, statuts de commande, canaux d'envoi (SMS, e-mail). "
            . "Appelle-le avant de modifier des étiquettes, groupes ou rendez-vous. Sans section : tout ; avec section : une seule partie (plus léger).";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'section' => ['type' => 'string', 'enum' => self::SECTIONS, 'description' => 'Une seule partie de la configuration (tout par défaut).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'crm';
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
        $sections = isset($input['section']) ? [$input['section']] : self::SECTIONS;
        $projectId = $context->project->id;
        $data = [];

        foreach ($sections as $section) {
            $data[$section] = match ($section) {
                'labels' => $this->labels($projectId),
                'groups' => Group::query()->where('project_id', $projectId)->orderBy('name')->get(['id', 'name'])->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])->all(),
                'calendars' => Calendar::query()->where('project_id', $projectId)->orderBy('name')->get(['id', 'name', 'type'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'type' => $c->type])->all(),
                'fields' => Field::query()->where('project_id', $projectId)->where('for', 'prospect')->orderBy('order')->get(['slug', 'name', 'type', 'meta', 'required'])
                    ->map(fn ($f) => ['slug' => $f->slug, 'name' => $f->name, 'type' => $f->type, 'custom' => (bool) $f->meta, 'required' => (bool) $f->required])->all(),
                'order_statuses' => OrderStatus::query()->where('project_id', $projectId)->orderBy('order')->get(['id', 'name'])->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->all(),
                'threads' => \App\Models\Thread::query()->where('project_id', $projectId)->orderBy('name')->get(['id', 'name', 'private'])->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'private' => (bool) $t->private])->all(),
                'messaging' => $this->messaging($projectId),
            };
        }

        return ToolResult::ok($data, 'Configuration du projet : ' . implode(', ', $sections), count($sections));
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function labels(int $projectId): array
    {
        return Category::query()->where('project_id', $projectId)->where('for', 'prospect')
            ->with(['labels' => fn ($query) => $query->whereNull('archived_at')->orderBy('order')->select('id', 'category_id', 'name')])
            ->orderBy('order')->get(['id', 'name', 'unique'])
            ->map(fn ($category) => [
                'category_id' => $category->id,
                'category' => $category->name,
                // A prospect holds at most one label of a "unique" category (a status)
                'one_label_per_prospect' => (bool) $category->unique,
                'labels' => $category->labels->map(fn ($label) => ['id' => $label->id, 'name' => $label->name])->all(),
            ])->all();
    }

    protected function messaging(int $projectId): array
    {
        return [
            'sms' => array_map(fn ($p) => ['ref' => $p['ref'], 'label' => $p['label']], $this->providers->forProject($projectId, ProviderRegistry::SMS)),
            'email_configured' => $this->providers->forProject($projectId, ProviderRegistry::EMAIL) !== [],
        ];
    }
}
