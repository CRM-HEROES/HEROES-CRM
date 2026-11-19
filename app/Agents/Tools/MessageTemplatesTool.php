<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\EventDescriptionTemplate;
use App\Models\MessageTemplate;
use App\Models\SmsTemplate;

/**
 * The ready-made texts of the project: SMS templates, e-mail templates and appointment description templates. They are
 * the business's own wording (with variables), not data of a prospect. Attachments are only counted.
 */
class MessageTemplatesTool implements AgentToolInterface
{
    public const KINDS = ['sms', 'email', 'appointment'];

    protected const BODY_LIMIT = 1500;

    public function key(): string
    {
        return 'message_templates';
    }

    public function description(): string
    {
        return "Modèles de textes du projet : kind=sms (modèles de SMS), kind=email (modèles de messages avec objet) ou kind=appointment (descriptions de rendez-vous). "
            . "Donne le nom et le texte tel qu'il est écrit, avec ses variables. Sert à rédiger dans le style de l'entreprise ou à retrouver un modèle ; ne modifie rien.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'kind' => ['type' => 'string', 'enum' => self::KINDS],
                'search' => ['type' => 'string', 'maxLength' => 100, 'description' => 'Une partie du nom.'],
            ],
            'required' => ['kind'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'template';
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
        $project = $context->project;
        $kind = $input['kind'];

        $query = match ($kind) {
            'sms' => SmsTemplate::query()->where('project_id', $project->id),
            'email' => MessageTemplate::query()->where('project_id', $project->id),
            'appointment' => EventDescriptionTemplate::query()->where('project_id', $project->id),
        };

        if (($search = trim((string) ($input['search'] ?? ''))) !== '') {
            $query->where('name', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr($search, 0, 100)) . '%');
        }

        $templates = $query->orderBy('order')->orderBy('name')->limit(50)->get();

        return ToolResult::ok(
            ['kind' => $kind, 'templates' => $templates->map(fn ($template) => $this->describe($kind, $template))->all()],
            'Modèles de ' . ['sms' => 'SMS', 'email' => 'messages', 'appointment' => 'descriptions de rendez-vous'][$kind],
            $templates->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function describe(string $kind, $template): array
    {
        $row = ['id' => $template->id, 'name' => mb_substr(strip_tags((string) $template->name), 0, 120), 'body' => $this->text($template->body)];

        if ($kind === 'email') {
            $row['subject'] = $this->text($template->subject, 200);
            // Only the number: the names and paths of the files stay out
            $attachments = json_decode((string) $template->getRawOriginal('attachments'), true);
            $row['attachments'] = is_array($attachments) ? count($attachments) : 0;
        }

        return $row;
    }

    protected function text(mixed $value, int $limit = self::BODY_LIMIT): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr($value, 0, $limit) : null;
    }
}
