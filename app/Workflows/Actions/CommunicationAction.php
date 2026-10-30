<?php

namespace App\Workflows\Actions;

use App\Models\Prospect;
use App\Models\WorkflowExecution;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\VariableResolver;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Base of the actions that talk to a prospect through an operator (email,
 * SMS, call).
 *
 * Operators
 *  The node only keeps a reference to an operator that the project already
 *  configures (config.provider, e.g. "sms:smsbox" or "line:12"). The
 *  credentials stay in the project settings and are never copied. The
 *  reference is checked when the workflow is published AND again when the
 *  communication is sent: an operator removed in between makes the node fail
 *  with a clear message instead of using something else.
 *
 * Rate limit
 *  At most N communications per minute and per operator (config
 *  workflows.rate_limits). Over the limit the node is postponed (see
 *  ThrottledAction), never run.
 *
 * Error handling
 *  By default an error stops the execution. With "on_error": "continue" on the
 *  node, the error is recorded and the workflow goes on with the next step.
 */
abstract class CommunicationAction implements ActionHandlerInterface, ThrottledAction, ConfigurableAction
{
    public function __construct(protected ProviderRegistry $providers)
    {
    }

    /**
     * email | sms | call
     */
    abstract protected function capability(): string;

    /**
     * Name of the action, for the messages ("Envoyer un SMS")
     */
    abstract protected function actionName(): string;

    /**
     * Texts of the configuration that can contain {{variables}}
     *
     * @return string[]
     */
    protected function textFields(): array
    {
        return [];
    }

    /**
     * Problems specific to the action
     *
     * @return string[]
     */
    protected function validateSpecific(array $config, ?int $projectId, string $label): array
    {
        return [];
    }

    public function validateConfig(array $config, ?int $projectId, string $label): array
    {
        $errors = [];
        $ref = $config['provider'] ?? '';

        if (!is_string($ref) || $ref === '') {
            $errors[] = "L'action \"$label\" ({$this->actionName()}) n'a pas d'opérateur : choisissez-en un.";
        } elseif ($projectId !== null && !$this->providers->find($projectId, $ref, $this->capability())) {
            $name = $config['provider_label'] ?? $ref;
            $errors[] = "L'action \"$label\" ({$this->actionName()}) utilise l'opérateur \"$name\", mais il n'est plus configuré pour ce projet.";
        }

        if (!in_array($config['on_error'] ?? 'fail', ['fail', 'continue'], true)) {
            $errors[] = "L'action \"$label\" a un mode d'erreur invalide.";
        }

        foreach ($this->textFields() as $field) {
            foreach (VariableResolver::unknown((string) ($config[$field] ?? '')) as $variable) {
                $errors[] = "L'action \"$label\" utilise une variable inconnue : {{" . $variable . '}}.';
            }
        }

        return array_merge($errors, $this->validateSpecific($config, $projectId, $label));
    }

    public function delay(WorkflowExecution $execution, array $config): ?CarbonInterface
    {
        $limit = (int) config('workflows.rate_limits.' . $this->capability());

        if ($limit <= 0) {
            return null;
        }

        $key = "workflows:rate:{$execution->project_id}:" . ($config['provider'] ?? '');

        if (RateLimiter::attempt($key, $limit, fn () => true, 60)) {
            return null;
        }

        // Retried within the next minute; spread so that a burst does not
        // come back all at once
        return now()->addSeconds(random_int(15, 75));
    }

    /**
     * The operator of the node, if it is still configured in the project
     *
     * @throws ProviderUnavailableException
     */
    protected function provider(WorkflowExecution $execution, array $config): array
    {
        $ref = (string) ($config['provider'] ?? '');
        $provider = $this->providers->find($execution->project_id, $ref, $this->capability());

        if (!$provider) {
            $name = $config['provider_label'] ?? $ref;

            throw new ProviderUnavailableException(
                "L'opérateur \"$name\" n'est plus configuré pour ce projet : {$this->actionName()} impossible."
            );
        }

        return $provider;
    }

    /**
     * The prospect to talk to: the subject itself, or the one of an order / appointment
     */
    protected function prospect(Model $subject): Prospect
    {
        $prospect = $subject instanceof Prospect ? $subject : $subject->prospect;

        if (!$prospect) {
            throw new \RuntimeException("Aucun prospect n'est lié à cet élément.");
        }

        return $prospect;
    }
}
