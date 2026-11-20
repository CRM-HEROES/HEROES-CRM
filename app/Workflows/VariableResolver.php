<?php

namespace App\Workflows;

use App\Models\Prospect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Variables of the messages: "Bonjour {{prospect.firstname}}".
 *
 * The only place where variables are understood. The names are a fixed list
 * (VARIABLES), not column names: nothing else of the prospect can leak into a
 * message, and a typo is reported when the workflow is published instead of
 * being sent. A known variable without value gives an empty text.
 *
 *   prospect  firstname lastname fullname email phone mobile company city postal_code country
 *             age plus_50 ("oui" from 50 years old, else "non") unsubscribe_url (the personal link
 *             that stops the mailings)
 *   user      firstname lastname fullname email     the first user assigned to the prospect
 *   project   name
 *   field     {{field.date_rdv}}: a custom field of the prospect (what the project added to its
 *             records), by its slug. The slug is not checked here (the fields are the project's own)
 *
 * Conditions, for the texts that differ by prospect:
 *
 *   {% if prospect.plus_50 == "oui" %} ... {% else %} ... {% endif %}
 *   {% if field.mensualite != "" %} ... {% endif %}
 *   {% if field.centre %} ... {% endif %}                (not empty)
 *
 * Output format: "text" (SMS) leaves the values as they are, "html" (emails)
 * escapes them, so a value can never inject markup in a message.
 *
 * (Not to be mistaken for the "{prospect.field}" placeholders of the CRM,
 * with single braces, which the message and SMS observers replace afterwards.)
 */
class VariableResolver
{
    public const PATTERN = '/\{\{\s*([a-z_]+)\.([a-z0-9_]+)\s*\}\}/i';

    /**
     * {% if group.name %}, {% if group.name == "x" %}, {% if group.name != "x" %}, {% else %}, {% endif %}
     */
    protected const TAG = '/\{%\s*(if\s+([a-z_]+)\.([a-z0-9_]+)(?:\s*(==|!=)\s*"([^"]*)")?|else|endif)\s*%\}/i';

    public const VARIABLES = [
        'prospect' => ['firstname', 'lastname', 'fullname', 'email', 'phone', 'mobile', 'company', 'city', 'postal_code', 'country', 'age', 'plus_50', 'unsubscribe_url'],
        'user' => ['firstname', 'lastname', 'fullname', 'email'],
        'project' => ['name'],
    ];

    /**
     * Groups whose names are the project's own (not a fixed list)
     */
    public const OPEN_GROUPS = ['field'];

    protected const PROSPECT_COLUMNS = [
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'email' => 'email',
        'phone' => 'phone_number',
        'mobile' => 'mobile_phone_number',
        'company' => 'company_name',
        'city' => 'city',
        'postal_code' => 'postal_code',
        'country' => 'country',
    ];

    protected ?array $user = null;
    protected ?string $projectName = null;

    public function __construct(protected Prospect $prospect)
    {
    }

    /**
     * Names of the variables of a text that do not exist ("prospect.nom"),
     * in the text and in its conditions
     *
     * @return string[]
     */
    public static function unknown(string $text): array
    {
        $found = [];

        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as [$all, $group, $name]) {
            $found[] = [$group, $name];
        }

        preg_match_all(self::TAG, $text, $tags, PREG_SET_ORDER);

        foreach ($tags as $tag) {
            if (!empty($tag[2])) {
                $found[] = [$tag[2], $tag[3]];
            }
        }

        $unknown = [];

        foreach ($found as [$group, $name]) {
            $group = strtolower($group);

            if (in_array($group, self::OPEN_GROUPS, true)) {
                continue;
            }

            if (!in_array(strtolower($name), self::VARIABLES[$group] ?? [], true)) {
                $unknown[] = strtolower("$group.$name");
            }
        }

        return array_values(array_unique($unknown));
    }

    /**
     * Are the conditions of the text well formed? (each "if" closed, no "else" or "endif" alone)
     *
     * @return string[] the problems found
     */
    public static function conditionProblems(string $text): array
    {
        preg_match_all(self::TAG, $text, $tags, PREG_SET_ORDER);

        $open = 0;
        $elses = [];
        $problems = [];

        foreach ($tags as $tag) {
            $kind = strtolower(explode(' ', trim($tag[1]))[0]);

            if ($kind === 'if') {
                $open++;
                $elses[$open] = false;
            } elseif ($kind === 'else') {
                if ($open === 0 || $elses[$open]) {
                    $problems[] = 'un « else » est mal placé';
                    break;
                }

                $elses[$open] = true;
            } else {
                if ($open === 0) {
                    $problems[] = 'un « endif » n\'a pas de « if »';
                    break;
                }

                $open--;
            }
        }

        if ($open > 0) {
            $problems[] = 'un « if » n\'est pas fermé par « endif »';
        }

        // A tag that looks like one but is not understood ({% for ... %}, {% if a.b > 2 %})
        preg_match_all('/\{%.*?%\}/s', $text, $all);

        if (count($all[0]) !== count($tags)) {
            $problems[] = 'une condition n\'est pas comprise (seuls « if », « else » et « endif », avec == ou !=, existent)';
        }

        return array_values(array_unique($problems));
    }

    /**
     * @param  string  $format  "text" | "html"
     */
    public function render(string $text, string $format = 'text'): string
    {
        $text = $this->conditions($text);

        return preg_replace_callback(self::PATTERN, function ($match) use ($format) {
            $value = $this->value(strtolower($match[1]), strtolower($match[2]));

            return $format === 'html' ? e($value) : $value;
        }, $text);
    }

    /**
     * Keeps the parts of the text whose condition is true
     */
    protected function conditions(string $text): string
    {
        if (!str_contains($text, '{%')) {
            return $text;
        }

        preg_match_all(self::TAG, $text, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $output = '';
        $cursor = 0;
        // One entry per open "if": [the part is kept, a branch was already kept, the parent keeps its content]
        $stack = [];
        $keeping = true;

        foreach ($tags as $tag) {
            [$whole, $offset] = $tag[0];

            if ($keeping) {
                $output .= substr($text, $cursor, $offset - $cursor);
            }

            $cursor = $offset + strlen($whole);
            $kind = strtolower(explode(' ', trim($tag[1][0]))[0]);

            if ($kind === 'if') {
                $parent = $keeping;
                $value = $parent ? $this->value(strtolower($tag[2][0]), strtolower($tag[3][0])) : '';
                $operator = $tag[4][0] ?? '';

                $true = match ($operator) {
                    '==' => $value === ($tag[5][0] ?? ''),
                    '!=' => $value !== ($tag[5][0] ?? ''),
                    default => $value !== '',
                };

                $stack[] = ['parent' => $parent, 'taken' => $parent && $true];
                $keeping = $parent && $true;
            } elseif ($kind === 'else' && $stack) {
                $top = &$stack[count($stack) - 1];
                $keeping = $top['parent'] && !$top['taken'];
                $top['taken'] = true;
                unset($top);
            } elseif ($kind === 'endif' && $stack) {
                $top = array_pop($stack);
                $keeping = $top['parent'];
            }
        }

        if ($keeping) {
            $output .= substr($text, $cursor);
        }

        return $output;
    }

    protected function value(string $group, string $name): string
    {
        switch ($group) {
            case 'prospect':
                if ($name === 'fullname') {
                    return (string) $this->prospect->full_name;
                }

                if ($name === 'age') {
                    $age = $this->age();

                    return $age === null ? '' : (string) $age;
                }

                if ($name === 'plus_50') {
                    $age = $this->age();

                    return $age !== null && $age >= 50 ? 'oui' : 'non';
                }

                if ($name === 'unsubscribe_url') {
                    return URL::signedRoute('mailing.unsubscribe', ['prospect' => $this->prospect->id]);
                }

                $column = self::PROSPECT_COLUMNS[$name] ?? null;

                return $column ? (string) $this->prospect->{$column} : '';

            case 'user':
                $user = $this->user();

                return match ($name) {
                    'firstname' => (string) ($user['name'] ?? ''),
                    'lastname' => (string) ($user['last_name'] ?? ''),
                    'fullname' => trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                    'email' => (string) ($user['email'] ?? ''),
                    default => '',
                };

            case 'project':
                return $name === 'name' ? (string) $this->projectName() : '';

            case 'field':
                // A custom field of the prospect: only text and numbers, whatever else it holds
                $value = data_get($this->prospect->meta, $name);

                return is_scalar($value) ? (string) $value : '';
        }

        return '';
    }

    /**
     * Age of the prospect in years, if the birth date is known
     */
    protected function age(): ?int
    {
        $birth = $this->prospect->date_of_birth;

        return $birth ? (int) \Carbon\Carbon::parse($birth)->age : null;
    }

    /**
     * First user assigned to the prospect: read once, only if a message uses it
     */
    protected function user(): array
    {
        return $this->user ??= (array) DB::table('prospect_user')
            ->join('users', 'users.id', '=', 'prospect_user.user_id')
            ->where('prospect_user.prospect_id', $this->prospect->id)
            ->orderBy('prospect_user.created_at')
            ->orderBy('prospect_user.user_id')
            ->first(['users.name', 'users.last_name', 'users.email']);
    }

    protected function projectName(): ?string
    {
        return $this->projectName ??= DB::table('projects')->where('id', $this->prospect->project_id)->value('name');
    }
}
