<?php

namespace App\Workflows\Actions;

use App\Console\Commands\Campaign as CampaignCommand;
use App\Models\CampaignAction;
use App\Models\WorkflowExecution;
use Illuminate\Database\Eloquent\Model;

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

        // Legacy handlers read the action value, and are stateful:
        // they are fed with a transient (never saved) action.
        $legacy->setAction(new CampaignAction(['action' => $type, 'value' => $config['value'] ?? []]));
        $legacy->setModel($subject);
        $legacy->handle();

        return ['handler' => 'campaign'];
    }

    protected function legacyHandlers(): array
    {
        return $this->legacy ??= app(CampaignCommand::class)->getActionHandlers();
    }
}
