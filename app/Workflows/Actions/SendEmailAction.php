<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowExecution;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\VariableResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * "Send an email" - { "provider": "email:project", "thread": 4, "body": "Bonjour {{prospect.firstname}}", "user": 7? }
 *
 * Goes through the CRM's own channel: a message is added to a discussion
 * channel (thread) of the prospect and the message observer sends it with the
 * email settings of the project. The message therefore appears in the
 * prospect's conversation, and its sending status (sent / error) is kept on
 * that message, exactly like an email written by a user. The id of the message
 * is recorded in the execution log.
 *
 * "thread" is optional (older workflows have one): without it the message goes
 * to the first public channel of the project, so nobody has to know what a
 * channel is. A project without any public channel cannot send emails.
 *
 * "user": the user whose answer is awaited (same as the campaign action).
 */
class SendEmailAction extends CommunicationAction
{
    protected function capability(): string
    {
        return ProviderRegistry::EMAIL;
    }

    protected function actionName(): string
    {
        return 'Envoyer un email';
    }

    protected function textFields(): array
    {
        return ['body'];
    }

    protected function validateSpecific(array $config, ?int $projectId, string $label): array
    {
        if (trim((string) ($config['body'] ?? '')) === '') {
            return ["L'action \"$label\" ({$this->actionName()}) n'a pas de contenu."];
        }

        if (!is_numeric($config['thread'] ?? null)) {
            if ($projectId !== null && !$this->defaultThread($projectId)) {
                return ["L'action \"$label\" ({$this->actionName()}) ne peut pas partir : le projet n'a aucun canal de discussion public (à créer dans les paramètres des messages)."];
            }

            return [];
        }

        if ($projectId !== null) {
            $thread = $this->thread($projectId, (int) $config['thread']);

            if (!$thread) {
                return ["Le canal de discussion de l'action \"$label\" n'existe plus dans ce projet."];
            }

            if ($thread->private) {
                return ["Le canal de discussion de l'action \"$label\" est privé : aucun email n'y est envoyé."];
            }
        }

        return [];
    }

    public function handle(Model $subject, array $config, WorkflowExecution $execution): array
    {
        $provider = $this->provider($execution, $config);
        $prospect = $this->prospect($subject);

        if (!$prospect->email) {
            throw new \RuntimeException("Le prospect n'a pas d'adresse email.");
        }

        $thread = is_numeric($config['thread'] ?? null)
            ? $this->thread($execution->project_id, (int) $config['thread'])
            : $this->defaultThread($execution->project_id);

        if (!$thread || $thread->private) {
            throw new \RuntimeException("Aucun canal de discussion public pour envoyer l'email (à créer dans les paramètres des messages).");
        }

        $message = $prospect->messages()->create([
            'body' => (new VariableResolver($prospect))->render((string) $config['body'], 'html'),
            'thread_id' => $thread->id,
            'from_user' => 1,
        ]);

        // Wait for the answer of a user (as the campaign action does)
        if (!empty($config['user'])) {
            $userId = DB::table('user_project')
                ->where('user_id', $config['user'])
                ->where('project_id', $prospect->project_id)
                ->value('user_id');

            if ($userId) {
                $message->users()->syncWithoutDetaching($userId);
            }
        }

        return ['provider' => $provider['ref'], 'message_id' => $message->id, 'thread_id' => $thread->id];
    }

    /**
     * The first public channel of the project, in the order of the settings
     */
    protected function defaultThread(int $projectId): ?object
    {
        return DB::table('threads')
            ->where('project_id', $projectId)
            ->where('private', 0)
            ->whereNull('deleted_at')
            ->orderBy('order')
            ->orderBy('id')
            ->first(['id', 'private']);
    }

    protected function thread(int $projectId, int $threadId): ?object
    {
        return DB::table('threads')
            ->where('id', $threadId)
            ->where('project_id', $projectId)
            ->first(['id', 'private']);
    }
}
