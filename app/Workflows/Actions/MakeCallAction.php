<?php

namespace App\Workflows\Actions;

use App\Models\Line;
use App\Models\WorkflowExecution;
use App\Services\CloudTalk;
use App\Services\KavkomService;
use App\Support\PhoneNumber;
use App\Workflows\Providers\ProviderRegistry;
use Illuminate\Database\Eloquent\Model;

/**
 * "Make a call" - { "provider": "line:12" }
 *
 * Uses a telephony line of the project, Kavkom or CloudTalk: the agent of the
 * line is called first, and is bridged to the prospect once they pick up. It
 * is the same call as the one of the softphone / call button:
 *
 *  - Kavkom: KavkomService::callFromConfig; the call is linked to the prospect
 *    and to the agent so its recording can be transcribed.
 *  - CloudTalk: CloudTalk::makeCall; the "call ended" webhook of CloudTalk
 *    records the call on the prospect (it matches it by the phone number).
 *
 * The call identifier, when the service gives one, is recorded in the
 * execution log. A call is never retried: a timeout can happen after the PBX
 * started it.
 */
class MakeCallAction extends CommunicationAction
{
    public function __construct(
        ProviderRegistry $providers,
        protected KavkomService $kavkom,
        protected CloudTalk $cloudTalk,
    ) {
        parent::__construct($providers);
    }

    protected function capability(): string
    {
        return ProviderRegistry::CALL;
    }

    protected function actionName(): string
    {
        return 'Effectuer un appel';
    }

    public function handle(Model $subject, array $config, WorkflowExecution $execution): array
    {
        $provider = $this->provider($execution, $config);
        $prospect = $this->prospect($subject);
        $number = $prospect->mobile_phone_number ?: $prospect->phone_number;

        if (!$number) {
            throw new \RuntimeException("Le prospect n'a pas de numéro de téléphone.");
        }

        // The line of the project that the registry just returned
        $line = Line::query()
            ->where('project_id', $execution->project_id)
            ->findOrFail($provider['line_id']);

        return $provider['operator'] === 'cloudtalk'
            ? $this->callWithCloudTalk($line, $provider, $number)
            : $this->callWithKavkom($line, $provider, $prospect->id, $number);
    }

    protected function callWithKavkom(Line $line, array $provider, int $prospectId, string $number): array
    {
        $lineConfig = (array) $line->config;
        $result = $this->kavkom->callFromConfig($lineConfig, $number);

        if (empty($result['success'])) {
            throw new \RuntimeException($result['message'] ?? "L'appel n'a pas pu être lancé.");
        }

        $this->kavkom->linkOutboundCall($result, $prospectId, $line->user_id, $lineConfig['domain_uuid'], $number);

        return ['provider' => $provider['ref'], 'call_uuid' => $result['call_uuid'] ?? null];
    }

    protected function callWithCloudTalk(Line $line, array $provider, string $number): array
    {
        // CloudTalk wants an international number (E.164). The CRM stores what
        // was typed ("06 11 22 33 44"): use the CRM's own conversion.
        $international = collect(PhoneNumber::candidates($number))
            ->first(fn ($candidate) => !str_starts_with($candidate, '0'));

        // Throws a RuntimeException with a readable message when CloudTalk refuses
        $response = $this->cloudTalk->makeCall(
            $line,
            $this->cloudTalk->formatCalleeNumber('+' . ($international ?? PhoneNumber::digits($number)))
        );

        return ['provider' => $provider['ref'], 'call_id' => $this->cloudTalk->callId($response)];
    }
}
