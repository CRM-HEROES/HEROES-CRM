<?php

namespace App\Workflows\Actions;

use App\Console\Commands\Campaign as CampaignCommand;
use App\Models\CampaignAction;
use App\Models\WorkflowExecution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The actions a workflow can run.
 *
 * New actions are registered with register(). Until they are ported, the
 * actions of the legacy campaigns (attach a label, send a message, ...) are
 * available as they are: they are not re-implemented, the existing handlers
 * are reused through an adapter.
 */
class ActionRegistry
{
    /**
     * @var array<string, ActionHandlerInterface>
     */
    protected array $handlers = [];

    /**
     * Legacy campaign handlers, by subject type then action
     */
    protected ?array $legacy = null;

    public function register(string $type, ActionHandlerInterface $handler): void
    {
        $this->handlers[$type] = $handler;
    }

    /**
     * A registered action (not the legacy campaign ones)
     */
    public function get(string $type): ?ActionHandlerInterface
    {
        return $this->handlers[$type] ?? null;
    }

    /**
     * Is the action available for this subject type?
     */
    public function has(string $type, string $subjectType): bool
    {
        return isset($this->handlers[$type]) || isset($this->legacyHandlers()[$subjectType][$type]);
    }

    /**
     * Run the action
     */
    public function handle(string $type, Model $subject, array $config, WorkflowExecution $execution): array
    {
        if (isset($this->handlers[$type])) {
            return $this->handlers[$type]->handle($subject, $config, $execution);
        }

        $legacy = $this->legacyHandlers()[$execution->subject_type][$type] ?? null;

        if (!$legacy) {
            throw new \RuntimeException("Unknown workflow action: $type");
        }

        $this->assertTargetExists($type, $config['value'] ?? [], $subject->project_id ?? $execution->project_id);

        // Legacy handlers read the action value, and are stateful:
        // they are fed with a transient (never saved) action.
        $legacy->setAction(new CampaignAction(['action' => $type, 'value' => $config['value'] ?? []]));
        $legacy->setModel($subject);
        $legacy->handle();

        return ['handler' => 'campaign'];
    }

    /**
     * The campaign handlers do nothing, silently, when the user / group / label
     * they are given does not exist (anymore) in the project. A step that did
     * nothing must not be reported as done: it fails, and says why.
     */
    protected function assertTargetExists(string $type, array $value, ?int $projectId): void
    {
        if (!str_starts_with($type, 'prospect-')) {
            return;
        }

        $messages = [
            'user' => "L'utilisateur choisi n'existe plus dans ce projet.",
            'group' => "Le groupe choisi n'existe plus dans ce projet.",
            'label' => "L'étiquette choisie n'existe plus dans ce projet.",
        ];

        foreach ($messages as $key => $message) {
            if (!str_ends_with($type, "-$key")) {
                continue;
            }

            $id = $value[$key] ?? null;

            $exists = $id && match ($key) {
                'user' => DB::table('user_project')->where('user_id', $id)->where('project_id', $projectId)->exists(),
                'group' => DB::table('groups')->where('id', $id)->where('project_id', $projectId)->whereNull('deleted_at')->exists(),
                'label' => DB::table('labels')
                    ->join('categories', 'categories.id', '=', 'labels.category_id')
                    ->where('labels.id', $id)
                    ->where('categories.project_id', $projectId)
                    ->exists(),
            };

            if (!$exists) {
                throw new \RuntimeException($message);
            }
        }
    }

    protected function legacyHandlers(): array
    {
        return $this->legacy ??= app(CampaignCommand::class)->getActionHandlers();
    }
}
