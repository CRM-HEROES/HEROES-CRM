<?php

namespace App\Workflows\Actions;

use App\Models\WorkflowExecution;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\VariableResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * "Send a SMS" - { "provider": "sms:smsbox", "message": "Bonjour {{prospect.firstname}}" }
 *
 * Goes through the CRM's own channel: a SMS is added to the prospect and the
 * SMS observer sends it through the gateway of the operator (SMSBOX, Ultramsg,
 * MTarget, Brevo, or CloudTalk), with the project settings. The SMS keeps its
 * sending status (sent_at / error) like any SMS written by a user; its id is
 * recorded in the execution log.
 *
 * CloudTalk sends from the line of an agent: the SMS is created on behalf of
 * the agent the line belongs to.
 */
class SendSmsAction extends CommunicationAction
{
    public function __construct(ProviderRegistry $providers)
    {
        parent::__construct($providers);
    }

    protected function capability(): string
    {
        return ProviderRegistry::SMS;
    }

    protected function actionName(): string
    {
        return 'Envoyer un SMS';
    }

    protected function textFields(): array
    {
        return ['message'];
    }

    protected function validateSpecific(array $config, ?int $projectId, string $label): array
    {
        if (trim((string) ($config['message'] ?? '')) === '') {
            return ["L'action \"$label\" ({$this->actionName()}) n'a pas de message."];
        }

        return [];
    }

    public function handle(Model $subject, array $config, WorkflowExecution $execution): array
    {
        $provider = $this->provider($execution, $config);
        $prospect = $this->prospect($subject);

        if (!$prospect->mobile_phone_number) {
            throw new \RuntimeException("Le prospect n'a pas de numéro de mobile.");
        }

        // Through a line (CloudTalk): sent by the agent of the line
        if (isset($provider['line_id']) && !$provider['user_id']) {
            throw new \RuntimeException("La ligne {$provider['label']} n'est affectée à aucun agent.");
        }

        $message = (new VariableResolver($prospect))->render((string) $config['message'], 'text');
        $sms = $prospect->sms()->create([
            'message' => $message,
            'source' => isset($provider['line_id']) ? $provider['operator'] : $provider['source'],
            'from_user' => true,
            'creator_id' => $provider['user_id'] ?? null,
        ]);

        return array_filter([
            'provider' => $provider['ref'],
            'sms_id' => $sms->id,
        ], fn ($value) => $value !== null);
    }
}
