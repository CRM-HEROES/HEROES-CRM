<?php

namespace App\Workflows\Mailing;

use App\Models\Workflow;
use App\Workflows\WorkflowGraphSanitizer;
use App\Workflows\WorkflowValidator;
use Illuminate\Support\Facades\DB;

/**
 * Puts the sequences of a mailing plan (the Careline kit) into the automations
 * of a project, as drafts: they are read and changed in the builder like any
 * other, and started with its "Activer" button once the emails are checked.
 *
 * What it does, for a project:
 *  - the lists of the plan become labels of the prospects (category "Parcours de mailing"):
 *    "add the contact to the list X" is "put the label X", and the sequence of the list starts
 *    when a prospect gets the label;
 *  - one automation per sequence of the catalogue: its emails are the templates imported by
 *    KitImporter (found by their codes), separated by the waits of the plan.
 *
 * It can be run again: an automation that was never published is redrawn, one that was
 * already published (or is running) is left alone, whatever was changed in it.
 */
class PlanGenerator
{
    public const CATEGORY = 'Parcours de mailing';
    public const STAGES = 'Étapes du parcours';
    public const ON_DEMAND = 'Envois à la demande';

    public function __construct(protected PlanBuilder $builder, protected WorkflowValidator $validator)
    {
    }

    /**
     * The sequences of the plan: what the catalogue says (the waits and the codes) and what only
     * a person reading it can tell (what makes a contact leave a sequence)
     *
     * @return array<int, array>
     */
    public static function specs(array $catalogue): array
    {
        // What makes a prospect leave each sequence (the exits of the plan), by the words of its name
        $rules = [
            'Premier contact' => [
                'stay' => [['field' => 'rdv_fixe', 'is_not' => 'oui'], ['field' => 'joint', 'is_not' => 'oui']],
                // PC3 leaves on day 3: day 4 without contact, the prospect goes on to the warm follow-up
                'then_wait' => 1,
                'then_label' => 'CL · Nurturing chaud',
            ],
            'Nurturing chaud' => [
                'stay' => [['field' => 'rdv_fixe', 'is_not' => 'oui']],
                'then_label' => 'CL · Nurturing froid',
            ],
            'Nurturing froid' => ['stay' => []],
            'Après le bilan' => [
                'stay' => [['field' => 'decision_bilan', 'is' => 'devis'], ['field' => 'ope_fixee', 'is_not' => 'oui']],
                // Enters the day of the assessment: the first email leaves the day after
                'first_wait' => 1,
            ],
        ];

        $specs = [];

        foreach ($catalogue['scenarios'] ?? [] as $scenario) {
            $rule = null;

            foreach ($rules as $words => $candidate) {
                if (str_contains((string) $scenario['nom'], $words)) {
                    $rule = $candidate;
                    break;
                }
            }

            if ($rule === null) {
                continue;
            }

            $specs[] = $rule + [
                'name' => $scenario['nom'],
                'label' => $scenario['liste_entree'],
                'steps' => array_map(fn ($step) => [
                    'days' => (int) $step['attendre_jours'],
                    'code' => $step['envoyer'],
                    'alternatives' => self::alternatives((string) ($step['variante'] ?? '')),
                ], $scenario['etapes']),
            ];
        }

        return $specs;
    }

    /**
     * The other emails of a step, from what the plan says about it:
     * "Si PLUS_50 = « oui » : envoyer N12-50 à la place"
     * "Si HC_MOTIF = « pas_eligible » : H03 ; si motif médical : H06"
     */
    public static function alternatives(string $variant): array
    {
        if (preg_match('/PLUS_50.*?envoyer\s+([A-Z0-9][A-Z0-9-]*)\s+à la place/u', $variant, $m)) {
            return [['field' => 'plus_50', 'is' => 'oui', 'code' => $m[1], 'label' => 'Le contact a 50 ans ou plus ?']];
        }

        if (preg_match('/HC_MOTIF.*?pas_eligible.*?:\s*([A-Z0-9][A-Z0-9-]*).*?médical\s*:\s*([A-Z0-9][A-Z0-9-]*)/u', $variant, $m)) {
            return [
                ['field' => 'hc_motif', 'is' => 'pas_eligible', 'code' => $m[1], 'label' => 'Motif noté : pas éligible ?'],
                ['field' => 'hc_motif', 'is' => 'medical', 'code' => $m[2], 'label' => 'Motif noté : médical ?'],
            ];
        }

        return [];
    }

    /**
     * @return array{labels: array, workflows: array<string, array{status: string, nodes: int, problems: array}>}
     */
    public function generate(int $projectId, string $kitPath, bool $dry = false): array
    {
        $catalogue = json_decode((string) @file_get_contents("$kitPath/catalogue.json"), true);

        abort_unless(is_array($catalogue), 422, "catalogue.json introuvable dans $kitPath");

        $templates = collect($catalogue['messages'] ?? [])->keyBy('code')->all();
        $specs = self::specs($catalogue);

        // The emails that are in no sequence: on a date, on a schedule, or on a label
        $covered = [];

        foreach ($specs as $spec) {
            foreach ($spec['steps'] as $step) {
                $covered[] = $step['code'];
                array_push($covered, ...array_column($step['alternatives'], 'code'));
            }
        }

        $flows = EventPlan::flows($catalogue, $covered);
        $labels = $this->allLabels($projectId, $catalogue['listes'] ?? [], $flows, $dry);
        $report = ['labels' => $labels, 'workflows' => []];

        foreach ($specs as $spec) {
            $graph = WorkflowGraphSanitizer::clean($this->builder->build($spec, $labels['ids'], $templates));

            $problems = $this->validator->validate($graph, $projectId);
            $existing = Workflow::where('project_id', $projectId)->where('name', $spec['name'])->first();

            if ($existing && ($existing->active_version_id || $existing->status !== Workflow::DRAFT)) {
                $status = 'laissée telle quelle (déjà publiée)';
            } elseif ($existing) {
                $status = 'redessinée';
                $dry || $existing->update(['draft_graph' => $graph]);
            } else {
                $status = 'créée';
                $dry || Workflow::create([
                    'project_id' => $projectId,
                    'name' => $spec['name'],
                    'description' => 'Séquence du plan de mailing : ' . count($spec['steps']) . ' emails. À vérifier, puis activer.',
                    'status' => Workflow::DRAFT,
                    'draft_graph' => $graph,
                ]);
            }

            $report['workflows'][$spec['name']] = ['status' => $status, 'nodes' => count($graph['nodes']), 'problems' => $problems];
        }

        foreach ($flows as $flow) {
            $graph = WorkflowGraphSanitizer::clean($this->builder->flow(
                $this->triggerConfig($flow['trigger'], $labels['ids']),
                $this->steps($flow['steps'], $labels['ids']),
                $templates,
                $flow['name']
            ));

            $report['workflows'][$flow['name']] = $this->save($projectId, $flow['name'], $graph, 'Envoi du plan de mailing : ' . implode(', ', $this->codesOf($flow)) . '. À vérifier, puis activer.', $dry);
        }

        return $report;
    }

    /**
     * Creates the automation, redraws it (a draft) or leaves it (published)
     *
     * @return array{status: string, nodes: int, problems: array}
     */
    protected function save(int $projectId, string $name, array $graph, string $description, bool $dry): array
    {
        $problems = $this->validator->validate($graph, $projectId);
        $existing = Workflow::where('project_id', $projectId)->where('name', $name)->first();

        if ($existing && ($existing->active_version_id || $existing->status !== Workflow::DRAFT)) {
            $status = 'laissée telle quelle (déjà publiée)';
        } elseif ($existing) {
            $status = 'redessinée';
            $dry || $existing->update(['draft_graph' => $graph]);
        } else {
            $status = 'créée';
            $dry || Workflow::create(['project_id' => $projectId, 'name' => $name, 'description' => $description, 'status' => Workflow::DRAFT, 'draft_graph' => $graph]);
        }

        return ['status' => $status, 'nodes' => count($graph['nodes']), 'problems' => $problems];
    }

    /**
     * The trigger of a flow, with the ids of its labels
     */
    protected function triggerConfig(array $trigger, array $ids): array
    {
        switch ($trigger['kind']) {
            case 'schedule':
                $config = ['trigger' => 'prospect.scheduled'] + $trigger['config'];
                $config['items'] = array_map(
                    fn ($item) => isset($item['label']) ? ['filters' => ['withLabels' => [$ids[$item['label']] ?? 0]]] : $item,
                    $config['items']
                );

                return $config;

            case 'created':
                return ['trigger' => 'prospect.created'];
        }

        // A label put on the prospect starts it, as many times as the label is put
        return [
            'trigger' => 'prospect.filter_entered',
            'op' => 'and',
            'items' => [['filters' => ['withLabels' => [$ids[$trigger['label']] ?? 0]]]],
            'exit_on_leave' => false,
            'once_per_subject' => false,
        ];
    }

    /**
     * The steps of a flow, with the ids of its labels
     */
    protected function steps(array $steps, array $ids): array
    {
        return array_map(function ($step) use ($ids) {
            if (isset($step['detach_label'])) {
                return ['detach' => $ids[$step['detach_label']] ?? 0, 'name' => $step['name'] ?? null];
            }

            return $step;
        }, $steps);
    }

    protected function codesOf(array $flow): array
    {
        $codes = [];

        foreach ($flow['steps'] as $step) {
            isset($step['send']) && $codes[] = $step['send'];

            foreach ($step['choose'] ?? [] as $choice) {
                $codes[] = $choice[1];
            }
        }

        return $codes;
    }

    /**
     * The labels of the plan: its lists, the stages of the prospect, and one per email sent on demand
     *
     * @return array{ids: array<string, int>, created: string[]}
     */
    protected function allLabels(int $projectId, array $lists, array $flows, bool $dry): array
    {
        $stages = [];
        $onDemand = [];

        foreach ($flows as $flow) {
            if (($flow['trigger']['kind'] ?? '') === 'label') {
                $name = $flow['trigger']['label'];
                str_starts_with($name, EventPlan::ON_DEMAND) ? $onDemand[] = $name : $stages[] = $name;
            }
        }

        $result = ['ids' => [], 'created' => []];

        foreach ([[self::CATEGORY, $lists], [self::STAGES, array_values(array_unique($stages))], [self::ON_DEMAND, array_values(array_unique($onDemand))]] as [$category, $names]) {
            if (!$names) {
                continue;
            }

            $part = $this->labels($projectId, $names, $dry, $category);
            $result['ids'] += $part['ids'];
            $result['created'] = array_merge($result['created'], $part['created']);
        }

        return $result;
    }

    /**
     * The lists of the plan as labels of the prospects (they exist or are made)
     *
     * @return array{ids: array<string, int>, created: string[]}
     */
    protected function labels(int $projectId, array $names, bool $dry, string $categoryName = self::CATEGORY): array
    {
        $category = DB::table('categories')
            ->where('project_id', $projectId)->where('for', 'prospect')->where('name', $categoryName)->whereNull('deleted_at')
            ->value('id');

        if (!$category && !$dry) {
            $category = DB::table('categories')->insertGetId([
                'project_id' => $projectId, 'name' => $categoryName, 'for' => 'prospect',
                'description' => 'Étiquettes du plan de mailing : un contact qui la reçoit déclenche l\'automatisation correspondante.',
                'color' => '#ffffff', 'bgcolor' => '#4a8796', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $existing = $category
            ? DB::table('labels')->where('category_id', $category)->whereNull('deleted_at')->pluck('id', 'name')->all()
            : [];

        $ids = [];
        $created = [];
        $order = 0;

        foreach ($names as $name) {
            $order++;

            if (isset($existing[$name])) {
                $ids[$name] = (int) $existing[$name];
                continue;
            }

            $created[] = $name;

            $ids[$name] = $dry || !$category ? 0 : (int) DB::table('labels')->insertGetId([
                'category_id' => $category, 'name' => $name, 'color' => '#ffffff', 'bgcolor' => '#4a8796',
                'order' => $order, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return ['ids' => $ids, 'created' => $created];
    }
}
