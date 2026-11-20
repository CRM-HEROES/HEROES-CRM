<?php

namespace App\Workflows\Mailing;

use Illuminate\Support\Str;

/**
 * Draws the automation of a mailing sequence: the same graph a person would
 * build by hand in the builder (a trigger, then "wait, send an email" over and
 * over), so that it can be read and changed there.
 *
 * A sequence (spec):
 *
 *   name          the name of the automation
 *   label         the label that starts it (a prospect with it enters the sequence)
 *   stay          tests on the custom fields that must stay true: when one is not, the prospect
 *                 leaves the sequence and nothing more is sent  [{ field, is | is_not }]
 *   first_wait    days to wait before the first email (0: at once)
 *   steps         [{ days, code, name, alternatives }]: wait "days" days, then send the template
 *                 "code"; "alternatives" replace it for some prospects
 *                 [{ field, is, code, name }] (the first one that is true wins)
 *   then_label    label put on the prospect when the sequence is over (it enters the next one)
 *   then_wait     days between the last email and that label
 *
 * Why the exits are tests of the trigger and not a test before each email: a
 * trigger on a filter ("has the label and RDV_FIXE is not yes") stops the
 * sequence of a prospect as soon as he stops matching, whatever the step he
 * is at, even while he waits.
 */
class PlanBuilder
{
    protected const STEP = 156;     // vertical distance between two nodes (a node is 76 high)
    protected const SIDE = 300;     // horizontal distance of an alternative

    /**
     * What is added to the configuration of the emails of the flow being drawn (if_busy...)
     */
    protected array $emailExtra = [];

    /**
     * Steps that lead out of the flow when a test fails: [node, branch] pairs, hooked to the end
     */
    protected array $exits = [];

    protected array $nodes = [];
    protected array $edges = [];
    protected int $y = 60;
    protected int $count = 0;

    /**
     * @param  array<string, int>  $labels  label name => id
     * @param  array<string, array>  $templates  code => the message of the catalogue
     */
    public function build(array $spec, array $labels, array $templates = [], array $options = []): array
    {
        $this->nodes = [];
        $this->edges = [];
        $this->y = 60;
        $this->count = 0;

        $labelId = $labels[$spec['label']] ?? null;

        $items = [['filters' => ['withLabels' => $labelId ? [$labelId] : []]]];

        foreach ($spec['stay'] ?? [] as $test) {
            $items[] = $test;
        }

        $this->add('trigger', 'trigger', [
            'trigger' => 'prospect.filter_entered',
            'op' => 'and',
            'items' => $items,
            // A prospect who stops matching leaves the sequence; one who comes back later starts it again only once
            'exit_on_leave' => true,
            'once_per_subject' => true,
        ], $spec['name']);

        // What comes next to hook to: [node, branch]
        $tails = [['trigger', null]];

        if (($spec['first_wait'] ?? 0) > 0) {
            $tails = $this->wait($tails, $spec['first_wait']);
        }

        foreach ($spec['steps'] as $i => $step) {
            if ($i > 0 || ($step['days'] ?? 0) > 0) {
                $tails = $this->wait($tails, (int) $step['days']);
            }

            $tails = $this->send($tails, $step, $templates);
        }

        if (!empty($spec['then_label'])) {
            if (($spec['then_wait'] ?? 0) > 0) {
                $tails = $this->wait($tails, (int) $spec['then_wait']);
            }

            $tails = $this->attach($tails, $labels[$spec['then_label']] ?? null, $spec['then_label']);
        }

        $end = $this->add('end', 'end', [], 'Fin de la séquence');
        $this->hook($tails, $end);

        return ['nodes' => $this->nodes, 'edges' => $this->edges];
    }

    /**
     * A flow that is not a plain sequence: any trigger, then steps of these kinds:
     *
     *   ['wait' => [config of a wait node], 'name' => '...']
     *   ['send' => 'CODE', 'alternatives' => [...], 'extra' => ['if_busy' => 'skip']]
     *   ['require' => condition tree, 'name' => '...']      goes on if true, ends the flow otherwise
     *   ['choose' => [[item, 'CODE'], ...]]                  the first test that is true sends its email, none: end
     *   ['detach' => label id, 'name' => '...']             takes a label off the prospect
     *
     * @param  array  $trigger  configuration of the trigger node
     */
    public function flow(array $trigger, array $steps, array $templates, string $name): array
    {
        $this->nodes = [];
        $this->edges = [];
        $this->y = 60;
        $this->count = 0;
        $this->exits = [];
        $this->emailExtra = [];

        $this->add('trigger', 'trigger', $trigger, $name);
        $tails = [['trigger', null]];

        foreach ($steps as $step) {
            if (isset($step['wait'])) {
                $id = $this->add('wait', 'wait', $step['wait'], $step['name'] ?? $this->describeWait($step['wait']));
                $this->hook($tails, $id);
                $tails = [[$id, null]];
            } elseif (isset($step['send'])) {
                $this->emailExtra = $step['extra'] ?? [];
                $tails = $this->send($tails, ['code' => $step['send'], 'alternatives' => $step['alternatives'] ?? []], $templates);
                $this->emailExtra = [];
            } elseif (isset($step['require'])) {
                $id = $this->add('condition', 'condition', $step['require'], $step['name'] ?? 'Condition');
                $this->hook($tails, $id);
                $this->exits[] = [$id, 'no'];
                $tails = [[$id, 'yes']];
            } elseif (isset($step['choose'])) {
                $tails = $this->choose($tails, $step['choose'], $templates);
            } elseif (isset($step['detach'])) {
                $id = $this->add('action', 'action', ['action' => 'prospect-detach-label', 'value' => ['label' => $step['detach']]], $step['name'] ?? 'Retirer l\'étiquette');
                $this->hook($tails, $id);
                $tails = [[$id, null]];
            }
        }

        $end = $this->add('end', 'end', [], 'Fin');
        $this->hook(array_merge($tails, $this->exits), $end);

        return ['nodes' => $this->nodes, 'edges' => $this->edges];
    }

    /**
     * "If this, send A; else if that, send B": one test after the other
     *
     * @param  array<int, array{0: array, 1: string, 2?: string}>  $choices  [test, code, name of the test]
     */
    protected function choose(array $tails, array $choices, array $templates): array
    {
        $out = [];

        foreach ($choices as $k => $choice) {
            [$test, $code] = $choice;
            $label = $choice[2] ?? null;

            $condition = $this->add('condition', 'condition', ['op' => 'and', 'items' => [$test]], $label ?? 'Condition');
            $this->hook($tails, $condition);

            $email = $this->email($code, $code, $templates, 80 + self::SIDE * ($k + 1), $this->y);
            $this->edges[] = ['from' => $condition, 'to' => $email, 'branch' => 'yes'];
            $out[] = [$email, null];

            $tails = [[$condition, 'no']];
        }

        // Nobody matched: nothing to send
        $this->exits = array_merge($this->exits, $tails);

        return $out;
    }

    protected function describeWait(array $config): string
    {
        if (isset($config['relative_to'])) {
            $anchor = ['next_event' => 'le rendez-vous', 'last_event' => 'le dernier rendez-vous', 'created' => 'la création'][$config['relative_to']] ?? '';
            $direction = ($config['direction'] ?? 'before') === 'after' ? 'après' : 'avant';

            return !empty($config['check_only'])
                ? "Vérifier que {$config['offset_days']} jours $direction $anchor est à venir"
                : "{$config['offset_days']} jours $direction $anchor";
        }

        foreach (['days' => 'jour(s)', 'hours' => 'heure(s)', 'minutes' => 'minute(s)'] as $unit => $name) {
            if (!empty($config[$unit])) {
                return "Attendre {$config[$unit]} $name";
            }
        }

        return 'Attendre';
    }

    /**
     * Wait some days
     */
    protected function wait(array $tails, int $days): array
    {
        $id = $this->add('wait', 'wait', ['days' => $days], $days === 1 ? 'Attendre 1 jour' : "Attendre $days jours");
        $this->hook($tails, $id);

        return [[$id, null]];
    }

    /**
     * Send the template of the step, or its alternative for the prospects that match one
     */
    protected function send(array $tails, array $step, array $templates): array
    {
        $alternatives = $step['alternatives'] ?? [];

        if (!$alternatives) {
            $id = $this->email($step['code'], $step['name'] ?? $step['code'], $templates);
            $this->hook($tails, $id);

            return [[$id, null]];
        }

        // "If the contact is plus 50: this one, else the usual one", one test after the other
        $out = [];

        foreach ($alternatives as $k => $alternative) {
            $condition = $this->add('condition', 'condition', [
                'op' => 'and',
                'items' => [['field' => $alternative['field'], 'is' => $alternative['is']]],
            ], $alternative['label'] ?? ($alternative['field'] . ' = ' . $alternative['is']));
            $this->hook($tails, $condition);

            $yes = $this->email($alternative['code'], $alternative['name'] ?? $alternative['code'], $templates, 80 + self::SIDE * ($k + 1), $this->y);
            $this->edges[] = ['from' => $condition, 'to' => $yes, 'branch' => 'yes'];
            $out[] = [$yes, null];

            // The other prospects go on to the next test (or to the usual email)
            $tails = [[$condition, 'no']];
        }

        $usual = $this->email($step['code'], $step['name'] ?? $step['code'], $templates);
        $this->hook($tails, $usual);
        $out[] = [$usual, null];

        return $out;
    }

    protected function attach(array $tails, ?int $labelId, string $name): array
    {
        $id = $this->add('action', 'action', ['action' => 'prospect-attach-label', 'value' => ['label' => $labelId]], "Ajouter à « $name »");
        $this->hook($tails, $id);

        return [[$id, null]];
    }

    protected function email(string $code, string $name, array $templates, ?int $x = null, ?int $y = null): string
    {
        $subject = $templates[$code]['nom_modele'] ?? $name;

        return $this->add('send', 'action', [
            'action' => 'send_email',
            'provider' => 'email:project',
            // The subject and the text come from the template: they are changed there, for everyone
            'template' => $code,
            'on_error' => 'continue',
        ] + $this->emailExtra, $this->label($code, (string) $subject), $x, $y);
    }

    /**
     * "CL NC1 · Votre demande, PRENOM : je vous appelle" -> "NC1 · Votre demande, PRENOM : je vous appelle"
     */
    protected function label(string $code, string $modelName): string
    {
        $name = trim(preg_replace('/^CL\s+/u', '', $modelName));

        return Str::limit($name !== '' ? $name : $code, 110, '…');
    }

    protected function hook(array $tails, string $to): void
    {
        foreach ($tails as [$from, $branch]) {
            $this->edges[] = $branch ? ['from' => $from, 'to' => $to, 'branch' => $branch] : ['from' => $from, 'to' => $to];
        }
    }

    protected function add(string $prefix, string $type, array $config, ?string $name = null, ?int $x = null, ?int $y = null): string
    {
        $id = $prefix === 'trigger' || $prefix === 'end' ? $prefix : $prefix . ++$this->count;

        $this->nodes[] = [
            'id' => $id,
            'type' => $type,
            'name' => $name,
            'config' => $config,
            'position' => ['x' => $x ?? 80, 'y' => $y ?? $this->y],
        ];

        // An alternative sits beside the usual one, on the same row
        if ($x === null) {
            $this->y += self::STEP;
        }

        return $id;
    }
}
