<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowExecution;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\VariableResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * "Send an email" - {
 *     "provider": "email:project",
 *     "subject": "Bienvenue {{prospect.firstname}}",
 *     "body": "<p>Bonjour {{prospect.firstname}}</p>",
 *     "cc_users": [3, 7], "cc_groups": [2],
 *     "thread": 4?
 * }
 *
 * Goes through the CRM's own channel: a message is added to a discussion
 * channel (thread) of the prospect and the message observer sends it with the
 * email settings of the project. The message therefore appears in the
 * prospect's conversation, and its sending status (sent / error) is kept on
 * that message, exactly like an email written by a user. The id of the message
 * is recorded in the execution log.
 *
 * "body" is the HTML written in the builder (cleaned before it is stored, see
 * EmailHtml). "subject" is optional: the CRM's usual subject is used without it.
 *
 * "cc_users" / "cc_groups": the users, and the members of the groups, who get
 * a copy. Their addresses are looked up when the email is sent, so a user who
 * joined the group since is included and one who left the project is not.
 *
 * "design": the look of the email, chosen in the builder (see EmailDesign). Without
 * it the email keeps the CRM's usual look.
 *
 * "thread" is optional (older workflows have one): without it the message goes
 * to the first public channel of the project, so nobody has to know what a
 * channel is. A project without any public channel cannot send emails.
 *
 * "user" (older workflows): the user whose answer is awaited.
 */
class SendEmailAction extends CommunicationAction
{
    /**
     * Most people put in copy of one email
     */
    protected const MAX_COPIES = 50;

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
        return ['body', 'subject'];
    }

    protected function validateSpecific(array $config, ?int $projectId, string $label): array
    {
        $errors = [];

        if (trim(strip_tags((string) ($config['body'] ?? ''), '<img>')) === '') {
            $errors[] = "L'action \"$label\" ({$this->actionName()}) n'a pas de contenu.";
        }

        if (mb_strlen((string) ($config['subject'] ?? '')) > 255) {
            $errors[] = "L'objet de l'action \"$label\" est trop long (255 caractères au plus).";
        }

        $errors = array_merge($errors, EmailDesign::problems($config['design'] ?? null, $label));

        foreach (['cc_users' => 'utilisateur', 'cc_groups' => 'groupe'] as $key => $noun) {
            $ids = $config[$key] ?? [];

            if (!is_array($ids) || count($ids) > self::MAX_COPIES || array_filter($ids, fn ($id) => !is_numeric($id))) {
                $errors[] = "La liste des {$noun}s en copie de l'action \"$label\" n'est pas valide.";
            }
        }

        if ($errors) {
            return $errors;
        }

        if ($projectId !== null) {
            $errors = array_merge($errors, $this->validateCopies($config, $projectId, $label));
        }

        if (!is_numeric($config['thread'] ?? null)) {
            if ($projectId !== null && !$this->defaultThread($projectId)) {
                $errors[] = "L'action \"$label\" ({$this->actionName()}) ne peut pas partir : le projet n'a aucun canal de discussion public (à créer dans les paramètres des messages).";
            }

            return $errors;
        }

        if ($projectId !== null) {
            $thread = $this->thread($projectId, (int) $config['thread']);

            if (!$thread) {
                $errors[] = "Le canal de discussion de l'action \"$label\" n'existe plus dans ce projet.";
            } elseif ($thread->private) {
                $errors[] = "Le canal de discussion de l'action \"$label\" est privé : aucun email n'y est envoyé.";
            }
        }

        return $errors;
    }

    /**
     * People in copy that are not part of the project (anymore)
     */
    protected function validateCopies(array $config, int $projectId, string $label): array
    {
        $errors = [];
        $users = array_map('intval', $config['cc_users'] ?? []);
        $groups = array_map('intval', $config['cc_groups'] ?? []);

        if ($users && DB::table('user_project')->where('project_id', $projectId)->whereIn('user_id', $users)->distinct()->count('user_id') !== count(array_unique($users))) {
            $errors[] = "Un utilisateur mis en copie de l'action \"$label\" ne fait plus partie de ce projet.";
        }

        if ($groups && DB::table('groups')->where('project_id', $projectId)->whereNull('deleted_at')->whereIn('id', $groups)->count() !== count(array_unique($groups))) {
            $errors[] = "Un groupe mis en copie de l'action \"$label\" n'existe plus dans ce projet.";
        }

        return $errors;
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

        $variables = new VariableResolver($prospect);
        $subjectLine = trim($variables->render((string) ($config['subject'] ?? ''), 'text'));
        $copies = $this->copies($config, $execution->project_id, $prospect->email);

        $message = $prospect->messages()->create([
            'body' => EmailHtml::clean($variables->render((string) $config['body'], 'html')),
            'subject' => $subjectLine !== '' ? mb_substr(preg_replace('/[\r\n]+/', ' ', $subjectLine), 0, 255) : null,
            'cc' => $copies ?: null,
            'email_layout' => isset($config['design']) ? $this->design($config['design'], $execution->project_id) : null,
            'thread_id' => $thread->id,
            'from_user' => 1,
        ]);

        // Wait for the answer of a user (older workflows)
        if (!empty($config['user'])) {
            $userId = DB::table('user_project')
                ->where('user_id', $config['user'])
                ->where('project_id', $prospect->project_id)
                ->value('user_id');

            if ($userId) {
                $message->users()->syncWithoutDetaching($userId);
            }
        }

        return [
            'provider' => $provider['ref'],
            'message_id' => $message->id,
            'thread_id' => $thread->id,
            'cc' => count($copies),
        ];
    }

    /**
     * The design chosen, complete (names and dates of the project are filled in)
     */
    protected function design(mixed $design, int $projectId): array
    {
        $project = DB::table('projects')->where('id', $projectId)->first(['name']);

        return EmailDesign::normalize($design, $project->name ?? null);
    }

    /**
     * The people in copy, from the users and the groups chosen: members of the
     * project with an address, each once, never the prospect itself
     *
     * @return array<int, array{email: string, name: ?string}>
     */
    protected function copies(array $config, int $projectId, string $prospectEmail): array
    {
        $users = array_map('intval', $config['cc_users'] ?? []);
        $groups = array_map('intval', $config['cc_groups'] ?? []);

        if (!$users && !$groups) {
            return [];
        }

        $query = DB::table('users')
            ->join('user_project', 'user_project.user_id', '=', 'users.id')
            ->where('user_project.project_id', $projectId)
            ->whereNotNull('users.email')
            ->where('users.email', '!=', '')
            ->where(function ($query) use ($users, $groups, $projectId) {
                if ($users) {
                    $query->whereIn('users.id', $users);
                }

                if ($groups) {
                    $query->orWhereIn('users.id', DB::table('user_group')
                        ->join('groups', 'groups.id', '=', 'user_group.group_id')
                        ->where('groups.project_id', $projectId)
                        ->whereNull('groups.deleted_at')
                        ->whereIn('user_group.group_id', $groups)
                        ->select('user_group.user_id'));
                }
            })
            ->orderBy('users.id');

        $seen = [strtolower($prospectEmail) => true];
        $copies = [];

        foreach ($query->get(['users.email', 'users.name', 'users.last_name']) as $user) {
            $key = strtolower($user->email);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $copies[] = ['email' => $user->email, 'name' => trim($user->name . ' ' . $user->last_name) ?: null];

            if (count($copies) >= self::MAX_COPIES) {
                break;
            }
        }

        return $copies;
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
