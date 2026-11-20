<?php

namespace App\Workflows\Mailing;

use App\Workflows\Actions\EmailHtml;
use App\Workflows\VariableResolver;
use Illuminate\Support\Facades\DB;

/**
 * Puts a mailing plan made for Brevo (the Careline kit: catalogue.json, one
 * HTML file per email, config.json) into a project of the CRM:
 *
 *  - its attributes become custom fields of the prospects (what the emails read);
 *  - its emails become templates of the project's library, found by their code;
 *  - the sending rules of the project are set (see MailingRules).
 *
 * The syntax of Brevo is turned into the CRM's:  {{ contact.PRENOM }} becomes
 * {{prospect.firstname}}, {{ contact.CENTRE }} becomes {{field.centre}},
 * {{ unsubscribe }} becomes the personal link of the prospect, the {URL_...}
 * of the plan take the addresses of config.json.
 *
 * It can be run again: what exists (by slug, by code) is updated, nothing is
 * duplicated.
 */
class KitImporter
{
    /**
     * Attributes of the plan that are not custom fields: the CRM knows them
     */
    protected const KNOWN = [
        'PRENOM' => 'prospect.firstname',
        'EMAIL' => 'prospect.email',
        'PLUS_50' => 'prospect.plus_50',
    ];

    /**
     * Fields the sending rules read, and the plan does not name
     */
    protected const RULE_FIELDS = [
        'mailing_optout' => 'Ne plus contacter (désinscrit)',
        'suppression_demandee' => 'Suppression des données demandée',
        // The score of the survey after the operation (G01 is sent for 4 or 5)
        'avis_enquete' => 'Avis de l\'enquête (1 à 5)',
    ];

    /**
     * What is left of the placeholders of the plan, and must be filled in before an import
     */
    protected const TO_VALIDATE = 'À VALIDER';

    /**
     * Brevo syntax -> the CRM's, for a text (a subject, a template)
     */
    public static function convert(string $text, array $config = []): string
    {
        // {{ unsubscribe }}: the personal link of the prospect
        $text = preg_replace('/\{\{\s*unsubscribe\s*\}\}/i', '{{prospect.unsubscribe_url}}', $text);

        // {{ contact.X }} and {% if contact.X ... %}
        $text = preg_replace_callback('/\{\{\s*contact\.([A-Z0-9_]+)\s*\}\}/', fn ($m) => '{{' . self::variable($m[1]) . '}}', $text);
        $text = preg_replace_callback('/\{%\s*(if)\s+contact\.([A-Z0-9_]+)/', fn ($m) => '{% if ' . self::variable($m[2]), $text);

        // {URL_RAPPEL}, {EMAIL_DPO}, {FOURCHETTE_PRIX}: what config.json says
        return preg_replace_callback('/\{([A-Z][A-Z0-9_]+)\}/', function ($m) use ($config) {
            $value = $config[$m[1]] ?? null;

            return is_string($value) && $value !== '' ? $value : $m[0];
        }, $text);
    }

    /**
     * "PRENOM" -> "prospect.firstname", "DATE_RDV" -> "field.date_rdv"
     */
    public static function variable(string $attribute): string
    {
        return self::KNOWN[$attribute] ?? 'field.' . strtolower($attribute);
    }

    /**
     * Problems of a converted text: what the CRM would not understand
     *
     * @return string[]
     */
    public static function problems(string $text): array
    {
        $problems = [];

        if (preg_match_all('/\{\{\s*contact\./', $text)) {
            $problems[] = 'une variable de Brevo n\'a pas été convertie';
        }

        if ($unknown = VariableResolver::unknown($text)) {
            $problems[] = 'variables inconnues : ' . implode(', ', $unknown);
        }

        foreach (VariableResolver::conditionProblems($text) as $problem) {
            $problems[] = $problem;
        }

        if (preg_match_all('/\{[A-Z][A-Z0-9_]+\}/', $text, $left)) {
            $problems[] = 'réglage non renseigné dans config.json : ' . implode(', ', array_unique($left[0]));
        }

        return $problems;
    }

    /**
     * @param  array  $options  dry_run, allow_placeholder
     * @return array{fields: array, templates: array, skipped: array, problems: array, settings: bool}
     */
    public function import(int $projectId, string $kitPath, array $options = []): array
    {
        $catalogue = $this->json("$kitPath/catalogue.json");
        $config = $this->json("$kitPath/config.json");
        $dry = !empty($options['dry_run']);

        $report = ['fields' => [], 'templates' => ['created' => 0, 'updated' => 0], 'skipped' => [], 'problems' => [], 'settings' => false];

        if (!empty($config['FOURCHETTE_PRIX']) && str_contains((string) $config['FOURCHETTE_PRIX'], self::TO_VALIDATE) && empty($options['allow_placeholder'])) {
            $report['problems'][] = 'FOURCHETTE_PRIX n\'est pas validée : complétez config.json (ou --allow-placeholder pour un essai).';

            return $report;
        }

        $report['fields'] = $this->fields($projectId, array_keys($catalogue['attributs'] ?? []), $catalogue['attributs_description'] ?? [], $dry);

        $order = (int) DB::table('message_templates')->where('project_id', $projectId)->max('order');

        foreach ($catalogue['messages'] ?? [] as $message) {
            $code = $message['code'];
            $file = $message['fichier_html'] ?? null;

            // The vocal messages and the short texts are not emails
            if (!$file || !is_file("$kitPath/$file")) {
                $report['skipped'][$code] = 'pas un email (' . ($message['canal'] ?? '?') . ')';
                continue;
            }

            $body = self::convert(file_get_contents("$kitPath/$file"), $config);
            $subject = self::convert((string) ($message['objet'] ?? ''), $config);

            // The title of the page of the file is the subject: nothing else to keep
            $problems = array_merge(self::problems($body), self::problems($subject));

            if ($problems) {
                $report['problems'][$code] = $problems;
                continue;
            }

            $values = [
                'name' => mb_substr((string) ($message['nom_modele'] ?? $code), 0, 190),
                'subject' => mb_substr($subject, 0, 255),
                'body' => $body,
                'code' => $code,
                'marketing' => !empty($message['marketing']),
                'disabled' => ($message['statut'] ?? 'actif') !== 'actif',
            ];

            $existing = DB::table('message_templates')->where('project_id', $projectId)->where('code', $code)->whereNull('deleted_at')->first(['id']);

            if ($existing) {
                $report['templates']['updated']++;
                $dry || DB::table('message_templates')->where('id', $existing->id)->update($values + ['updated_at' => now()]);
            } else {
                $report['templates']['created']++;
                $dry || DB::table('message_templates')->insert($values + ['project_id' => $projectId, 'order' => ++$order, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $report['settings'] = $this->rules($projectId, $dry);

        return $report;
    }

    /**
     * The attributes of the plan as custom fields of the prospects, plus the ones of the rules
     *
     * @return string[] the slugs created
     */
    protected function fields(int $projectId, array $attributes, array $descriptions, bool $dry): array
    {
        $wanted = [];

        foreach ($attributes as $attribute) {
            if (!isset(self::KNOWN[$attribute])) {
                $wanted[strtolower($attribute)] = [$attribute, $descriptions[$attribute] ?? null];
            }
        }

        foreach (self::RULE_FIELDS as $slug => $name) {
            $wanted[$slug] = [$name, null];
        }

        $existing = DB::table('fields')->where('project_id', $projectId)->where('for', 'prospect')->whereNull('deleted_at')->pluck('slug')->all();
        $created = [];
        $order = (int) DB::table('fields')->where('project_id', $projectId)->max('order');

        foreach ($wanted as $slug => [$name, $description]) {
            if (in_array($slug, $existing, true)) {
                continue;
            }

            $created[] = $slug;

            $dry || DB::table('fields')->insert([
                'project_id' => $projectId,
                'slug' => $slug,
                'name' => mb_substr($name, 0, 50),
                'description' => $description,
                'type' => 'text',
                'for' => 'prospect',
                'meta' => 1,
                'order' => ++$order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $created;
    }

    /**
     * The sending rules of the project, if it has none yet (existing ones are kept)
     */
    protected function rules(int $projectId, bool $dry): bool
    {
        if (DB::table('project_settings')->where('project_id', $projectId)->where('key', MailingRules::KEY)->exists()) {
            return false;
        }

        $dry || DB::table('project_settings')->insert([
            'project_id' => $projectId,
            'key' => MailingRules::KEY,
            'value' => json_encode(array_diff_key(MailingRules::DEFAULTS, ['optout_labels' => 1])),
        ]);

        return true;
    }

    protected function json(string $path): array
    {
        abort_unless(is_file($path), 422, "Fichier introuvable : $path");

        return json_decode(file_get_contents($path), true) ?: [];
    }
}
