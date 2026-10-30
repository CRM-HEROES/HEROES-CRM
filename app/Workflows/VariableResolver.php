<?php

namespace App\Workflows;

use App\Models\Prospect;
use Illuminate\Support\Facades\DB;

/**
 * Variables of the messages: "Bonjour {{prospect.firstname}}".
 *
 * The only place where variables are understood. The names are a fixed list
 * (VARIABLES), not column names: nothing else of the prospect can leak into a
 * message, and a typo is reported when the workflow is published instead of
 * being sent. A known variable without value gives an empty text.
 *
 *   prospect  firstname lastname fullname email phone mobile company city postal_code country
 *   user      firstname lastname fullname email     the first user assigned to the prospect
 *   project   name
 *
 * Output format: "text" (SMS) leaves the values as they are, "html" (emails)
 * escapes them, so a value can never inject markup in a message.
 *
 * (Not to be mistaken for the "{prospect.field}" placeholders of the CRM,
 * with single braces, which the message and SMS observers replace afterwards.)
 */
class VariableResolver
{
    public const PATTERN = '/\{\{\s*([a-z_]+)\.([a-z_]+)\s*\}\}/i';

    public const VARIABLES = [
        'prospect' => ['firstname', 'lastname', 'fullname', 'email', 'phone', 'mobile', 'company', 'city', 'postal_code', 'country'],
        'user' => ['firstname', 'lastname', 'fullname', 'email'],
        'project' => ['name'],
    ];

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
     * Names of the variables of a text that do not exist ("prospect.nom")
     *
     * @return string[]
     */
    public static function unknown(string $text): array
    {
        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER);

        $unknown = [];

        foreach ($matches as [$all, $group, $name]) {
            if (!in_array(strtolower($name), self::VARIABLES[strtolower($group)] ?? [], true)) {
                $unknown[] = strtolower("$group.$name");
            }
        }

        return array_values(array_unique($unknown));
    }

    /**
     * @param  string  $format  "text" | "html"
     */
    public function render(string $text, string $format = 'text'): string
    {
        return preg_replace_callback(self::PATTERN, function ($match) use ($format) {
            $value = $this->value(strtolower($match[1]), strtolower($match[2]));

            return $format === 'html' ? e($value) : $value;
        }, $text);
    }

    protected function value(string $group, string $name): string
    {
        switch ($group) {
            case 'prospect':
                if ($name === 'fullname') {
                    return (string) $this->prospect->full_name;
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
        }

        return '';
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
