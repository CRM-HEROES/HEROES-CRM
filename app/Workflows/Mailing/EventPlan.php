<?php

namespace App\Workflows\Mailing;

/**
 * The emails of a mailing plan that are not a plain sequence: the ones that
 * leave on a date (before / after an appointment), on a schedule (the
 * newsletter) or when someone decides (a label put by an advisor).
 *
 * Each becomes an automation of its own, so that each can be read, paused and
 * followed on its own. A flow is a definition the generator turns into a graph:
 *
 *   name       the name of the automation
 *   trigger    ['kind' => 'label', 'label' => name]                 a prospect gets the label
 *              ['kind' => 'schedule', 'config' => [...]]              the clock (the audience is in the config)
 *              ['kind' => 'created']                                  a prospect is created
 *   steps      what PlanBuilder::flow() draws, labels by their names ('detach_label')
 *
 * The moments come from the plan ("7 jours avant le bilan", "J+45", "Janvier, 1er numéro"):
 * the table below is that text, made data. What the plan only says in words
 * ("lead noté hésitant", "avis de 4 ou 5") is a test on a custom field of the
 * prospect, named after the attribute.
 */
class EventPlan
{
    public const NEWSLETTER_LIST = 'CL · Tous les contacts (newsletter)';
    public const ON_DEMAND = 'Envoi · ';

    // The stages of a prospect, put by the CRM (or an advisor) when it happens
    public const STAGE_BILAN = 'CL · Bilan fixé';
    public const STAGE_OPE = 'CL · Intervention fixée';
    public const STAGE_CONTROLE = 'CL · Contrôle fixé';
    public const STAGE_RAPPEL = 'CL · Rappel convenu';

    /**
     * How the automations around a stage are called
     */
    protected const STAGE_WORDS = [
        self::STAGE_BILAN => ['before' => 'Avant le bilan', 'after' => 'Après le bilan'],
        self::STAGE_OPE => ['before' => 'Avant l\'intervention', 'after' => 'Après l\'intervention'],
        self::STAGE_CONTROLE => ['before' => 'Avant le contrôle', 'after' => 'Après le contrôle'],
        self::STAGE_RAPPEL => ['before' => 'Avant le rappel', 'after' => 'Après le rappel'],
    ];

    /**
     * The newsletter goes out on these days of the month, at this hour, over this many minutes
     */
    public const ISSUE_DAYS = [1 => 8, 2 => 22];
    public const ISSUE_AT = '11:00';
    public const ISSUE_WINDOW = 60;

    protected const MONTHS = [
        'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6,
        'juillet' => 7, 'août' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12,
    ];

    /**
     * Emails sent "n days before / after the appointment of the stage": code => [stage, direction, days, name of the flow]
     * (an appointment of the prospect is the date: the bilan, the operation, the check-up...)
     */
    protected const AROUND = [
        'AB3' => [self::STAGE_BILAN, 'before', 7],
        'AB4' => [self::STAGE_BILAN, 'before', 5],
        'AB5' => [self::STAGE_BILAN, 'before', 3],
        'AB6' => [self::STAGE_BILAN, 'before', 2],
        'AB7' => [self::STAGE_BILAN, 'before', 1],
        'Q04' => [self::STAGE_RAPPEL, 'before', 2],
        'O2' => [self::STAGE_OPE, 'before', 14],
        'OP2' => [self::STAGE_OPE, 'before', 7],
        'OP3' => [self::STAGE_OPE, 'after', 1],
        'OP4' => [self::STAGE_OPE, 'after', 30],
        'G01' => [self::STAGE_OPE, 'after', 45],
        'G02' => [self::STAGE_OPE, 'after', 120],
        'P04' => [self::STAGE_CONTROLE, 'before', 2],
    ];

    /**
     * @param  string[]  $covered  the codes already in a sequence
     * @return array<int, array>
     */
    public static function flows(array $catalogue, array $covered = []): array
    {
        $messages = collect($catalogue['messages'] ?? [])->keyBy('code');
        $usable = fn (string $code) => isset($messages[$code]) && !empty($messages[$code]['fichier_html']) && ($messages[$code]['statut'] ?? 'actif') === 'actif';
        $flows = [];
        $name = fn (string $code, string $what) => "CL · $what · $code";

        // Right after the stage: the confirmation
        foreach (['AB1' => [self::STAGE_BILAN, 'Avant le bilan'], 'OP1' => [self::STAGE_OPE, 'Intervention']] as $code => [$stage, $what]) {
            if ($usable($code)) {
                $flows[] = ['name' => $name($code, $what), 'trigger' => ['kind' => 'label', 'label' => $stage], 'steps' => [['send' => $code]]];
            }
        }

        // The day after the prospect gets the stage, if the appointment is not too close
        if ($usable('AB2')) {
            $flows[] = ['name' => $name('AB2', 'Avant le bilan'), 'trigger' => ['kind' => 'label', 'label' => self::STAGE_BILAN], 'steps' => [
                ['wait' => ['days' => 1]],
                // "Bilan dans 3 jours ou plus": a day later, the appointment must still be 2 days away
                ['wait' => ['relative_to' => 'next_event', 'offset_days' => 2, 'direction' => 'before', 'check_only' => true]],
                ['send' => 'AB2'],
            ]];
        }

        // Before / after an appointment
        foreach (self::AROUND as $code => [$stage, $direction, $days]) {
            if (!$usable($code)) {
                continue;
            }

            $steps = [['wait' => ['relative_to' => 'next_event', 'offset_days' => $days, 'direction' => $direction, 'at' => '09:00', 'if_past' => 'end']]];

            if ($code === 'AB4') {
                // For the prospects noted as hesitating (HESITATION: faible, moyen or fort)
                array_unshift($steps, ['require' => ['op' => 'and', 'items' => [['field' => 'hesitation', 'filled' => true], ['field' => 'hesitation', 'is_not' => 'faible']]], 'name' => 'Le contact hésite (moyen ou fort) ?']);
            }

            if ($code === 'G01') {
                // "Si avis de 4 ou 5 à l'enquête"
                $steps[] = ['require' => ['op' => 'or', 'items' => [['field' => 'avis_enquete', 'is' => '4'], ['field' => 'avis_enquete', 'is' => '5']]], 'name' => 'Avis de 4 ou 5 à l\'enquête ?'];
            }

            $steps[] = ['send' => $code];

            $flows[] = ['name' => $name($code, self::STAGE_WORDS[$stage][$direction]), 'trigger' => ['kind' => 'label', 'label' => $stage], 'steps' => $steps];
        }

        // One year after the operation: two emails
        if ($usable('P06')) {
            $flows[] = ['name' => $name('P06', 'Un an après l\'intervention'), 'trigger' => ['kind' => 'label', 'label' => self::STAGE_OPE], 'steps' => array_filter([
                ['wait' => ['relative_to' => 'next_event', 'offset_days' => 365, 'direction' => 'after', 'at' => '09:00', 'if_past' => 'end']],
                ['send' => 'P06'],
                $usable('G05') ? ['send' => 'G05'] : null,
            ])];
        }

        // The day after the bilan, by what was decided
        $decisions = array_values(array_filter([
            $usable('PB7') ? [['field' => 'decision_bilan', 'is' => 'non_eligible'], 'PB7', 'Laser non adapté ?'] : null,
            $usable('PB8') ? [['field' => 'decision_bilan', 'is' => 'autre_solution'], 'PB8', 'Une autre solution (implant, cataracte...) ?'] : null,
            $usable('D07') ? [['field' => 'decision_bilan', 'is' => 'refus'], 'D07', 'Devis refusé ?'] : null,
            $usable('D02') ? [['field' => 'decision_bilan', 'is' => 'plus_tard'], 'D02', 'Éligible plus tard ?'] : null,
        ]));

        if ($decisions) {
            $flows[] = ['name' => 'CL · Lendemain du bilan · décision', 'trigger' => ['kind' => 'label', 'label' => self::STAGE_BILAN], 'steps' => [
                ['wait' => ['relative_to' => 'next_event', 'offset_days' => 1, 'direction' => 'after', 'at' => '09:00', 'if_past' => 'end']],
                ['choose' => $decisions],
            ]];
        }

        // The newsletter: one automation per issue, on its day
        $newsletter = self::newsletter($catalogue);

        foreach ($newsletter['issues'] as $code => [$month, $issue]) {
            if (!$usable($code)) {
                continue;
            }

            $flows[] = [
                'name' => sprintf('CL · Newsletter · %s n°%d · %s', array_search($month, self::MONTHS), $issue, $code),
                'trigger' => ['kind' => 'schedule', 'config' => [
                    'months' => [$month],
                    'days' => [self::ISSUE_DAYS[$issue]],
                    'at' => self::ISSUE_AT,
                    'window_minutes' => self::ISSUE_WINDOW,
                    'op' => 'and',
                    'items' => [['label' => self::NEWSLETTER_LIST], ['field' => 'mailing_optout', 'is_not' => 'oui']],
                ]],
                // A newsletter that is a day late is not sent
                'steps' => [['send' => $code, 'extra' => ['if_busy' => 'skip'], 'alternatives' => $newsletter['alternatives'][$code] ?? []]],
            ];
        }

        // Months after the request: they replace the issue of the fortnight
        foreach (['L12' => 182, 'L20' => 365] as $code => $days) {
            if ($usable($code)) {
                $flows[] = ['name' => $name($code, $days === 182 ? '6 mois après la demande' : '1 an après la demande'), 'trigger' => ['kind' => 'created'], 'steps' => [
                    ['wait' => ['relative_to' => 'created', 'offset_days' => $days, 'direction' => 'after', 'at' => self::ISSUE_AT, 'if_past' => 'end']],
                    ['send' => $code, 'extra' => ['if_busy' => 'skip']],
                ]];
            }
        }

        // The others wait for their label: an advisor puts it, the email leaves, the label goes
        foreach ($messages as $code => $message) {
            if (in_array($code, $covered, true) || in_array($code, self::planned($flows), true) || in_array($code, array_keys($newsletter['variants']), true) || !$usable($code)) {
                continue;
            }

            if (($message['declenchement'] ?? '') === 'scenario_brevo' || in_array($code, array_keys($newsletter['issues']), true)) {
                continue;
            }

            $steps = [];

            if ($code === 'RM1') {
                // "30 minutes après un rendez-vous manqué"
                $steps[] = ['wait' => ['minutes' => 30]];
            }

            $steps[] = ['send' => $code];
            $steps[] = ['detach_label' => self::ON_DEMAND . $code, 'name' => 'Retirer l\'étiquette d\'envoi'];

            $flows[] = ['name' => $name($code, 'Envoi à la demande'), 'trigger' => ['kind' => 'label', 'label' => self::ON_DEMAND . $code], 'steps' => $steps];
        }

        return $flows;
    }

    /**
     * Codes sent by the flows already drawn
     */
    protected static function planned(array $flows): array
    {
        $codes = [];

        foreach ($flows as $flow) {
            foreach ($flow['steps'] as $step) {
                foreach (['send'] as $key) {
                    if (isset($step[$key])) {
                        $codes[] = $step[$key];
                    }
                }

                foreach ($step['choose'] ?? [] as $choice) {
                    $codes[] = $choice[1];
                }

                foreach ($step['alternatives'] ?? [] as $alternative) {
                    $codes[] = $alternative['code'];
                }
            }
        }

        return $codes;
    }

    /**
     * The issues of the newsletter, read in the plan ("Janvier, 1er numéro"), and the emails that
     * replace one for some contacts ("remplace N05 pour les + 50 ans")
     *
     * @return array{issues: array<string, array{0: int, 1: int}>, variants: array<string, string>, alternatives: array<string, array>}
     */
    public static function newsletter(array $catalogue): array
    {
        $issues = [];
        $variants = [];
        $alternatives = [];

        foreach ($catalogue['messages'] ?? [] as $message) {
            if (($message['declenchement'] ?? '') !== 'campagne_newsletter') {
                continue;
            }

            $when = (string) ($message['quand'] ?? '');

            // An email that replaces another one for part of the contacts
            if (preg_match('/remplace\s+([A-Z0-9][A-Z0-9-]*)\s+pour les\s+(.+)$/u', $when, $m)) {
                $variants[$message['code']] = $m[1];
                $alternatives[$m[1]][] = [
                    'field' => str_contains($m[2], 'sans correction') ? 'sans_correction' : 'plus_50',
                    'is' => 'oui',
                    'code' => $message['code'],
                    'label' => str_contains($m[2], 'sans correction') ? 'Sans lunettes ni lentilles ?' : 'Le contact a 50 ans ou plus ?',
                ];
                continue;
            }

            if (preg_match('/^(\p{L}+),\s*(1er|2e)\s+num/u', $when, $m) && isset(self::MONTHS[mb_strtolower($m[1])])) {
                $issues[$message['code']] = [self::MONTHS[mb_strtolower($m[1])], $m[2] === '1er' ? 1 : 2];
            }
        }

        return ['issues' => $issues, 'variants' => $variants, 'alternatives' => $alternatives];
    }
}
