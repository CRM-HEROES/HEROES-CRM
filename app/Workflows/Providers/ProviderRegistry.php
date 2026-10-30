<?php

namespace App\Workflows\Providers;

use Illuminate\Support\Facades\DB;

/**
 * The communication operators of a project, as the workflows see them.
 *
 * It is a READ-ONLY view of what the project settings already configure: the
 * email settings, the SMS gateways (SMSBOX, Ultramsg, MTarget, Brevo) and the
 * telephony lines (Kavkom, CloudTalk, ...). It stores nothing and exposes no
 * credential: a workflow node only keeps the reference ("ref") of an
 * operator, e.g. "sms:smsbox" or "line:12", and the credentials are read from
 * the project configuration at the time of the communication.
 *
 * Capabilities are not described anywhere in the CRM, so they are declared
 * here. Only what a workflow can really do is listed: a gateway is offered for
 * an action only if the CRM can send that kind of communication through it.
 *
 *   email   the project email settings (SMTP or Brevo)
 *   sms     SMSBOX, Ultramsg, MTarget, Brevo SMS; a CloudTalk line with a SMS number
 *   call    a Kavkom line, or a CloudTalk line with its API keys and agent (the
 *           agent is called first, then the prospect)
 *
 * (Kavkom has no SMS integration in the CRM, so it is not offered for SMS.)
 */
class ProviderRegistry
{
    public const EMAIL = 'email';
    public const SMS = 'sms';
    public const CALL = 'call';

    public const CAPABILITIES = [self::EMAIL, self::SMS, self::CALL];

    public const EMAIL_REF = 'email:project';

    /**
     * SMS gateways configured in the project settings (setting key => label)
     */
    protected const SMS_GATEWAYS = [
        'smsbox' => 'SMSBOX',
        'ultramsg' => 'Ultramsg',
        'mtarget' => 'MTarget',
        'brevo' => 'Brevo SMS',
    ];

    /**
     * Operators of a project, optionally only the ones with a capability.
     * Two queries (settings, lines).
     *
     * @return array<int, array{ref: string, label: string, operator: string, capabilities: string[], source?: string, line_id?: int, user_id?: int|null}>
     */
    public function forProject(int $projectId, ?string $capability = null): array
    {
        $providers = array_merge($this->fromSettings($projectId), $this->fromLines($projectId));

        if ($capability !== null) {
            $providers = array_values(array_filter(
                $providers,
                fn ($provider) => in_array($capability, $provider['capabilities'], true)
            ));
        }

        return $providers;
    }

    /**
     * The operator behind a reference, if it is still configured in this
     * project and has the capability. An operator of another project is
     * never returned.
     */
    public function find(int $projectId, string $ref, string $capability): ?array
    {
        foreach ($this->forProject($projectId, $capability) as $provider) {
            if ($provider['ref'] === $ref) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Description of an operator that is safe to send to the browser
     */
    public static function public(array $provider): array
    {
        return [
            'ref' => $provider['ref'],
            'label' => $provider['label'],
            'operator' => $provider['operator'],
            'capabilities' => $provider['capabilities'],
        ];
    }

    protected function fromSettings(int $projectId): array
    {
        $settings = DB::table('project_settings')
            ->where('project_id', $projectId)
            ->whereIn('key', array_merge(['email'], array_keys(self::SMS_GATEWAYS)))
            ->pluck('value', 'key')
            ->map(fn ($value) => is_string($value) ? json_decode($value, true) : $value);

        $providers = [];

        // The email settings of the project (or the platform ones, see ProjectMail)
        $email = $settings->get('email');

        if (!empty($email['api_key']) || !empty($email['host'])) {
            $providers[] = [
                'ref' => self::EMAIL_REF,
                'label' => !empty($email['api_key']) ? 'Brevo (email)' : 'SMTP ' . $email['host'],
                'operator' => !empty($email['api_key']) ? 'brevo' : 'smtp',
                'capabilities' => [self::EMAIL],
            ];
        } elseif (env('BREVO_API_KEY') && env('MAIL_FROM_ADDRESS')) {
            $providers[] = [
                'ref' => self::EMAIL_REF,
                'label' => 'Brevo (email)',
                'operator' => 'brevo',
                'capabilities' => [self::EMAIL],
            ];
        }

        foreach (self::SMS_GATEWAYS as $key => $label) {
            // Same test as the SMS observer: a configured setting
            if (!empty($settings->get($key))) {
                $providers[] = [
                    'ref' => "sms:$key",
                    'label' => $label,
                    'operator' => $key,
                    'capabilities' => [self::SMS],
                    'source' => $key,
                ];
            }
        }

        return $providers;
    }

    protected function fromLines(int $projectId): array
    {
        $providers = [];

        // The configuration is read to know what the line can do; it is not returned
        foreach (DB::table('lines')->where('project_id', $projectId)->orderBy('name')->get(['id', 'name', 'operator', 'user_id', 'config']) as $line) {
            $config = is_string($line->config) ? (json_decode($line->config, true) ?: []) : (array) $line->config;
            $capabilities = [];

            if ($line->operator === 'kavkom' && !empty($config['api_token']) && !empty($config['domain_uuid'])) {
                $capabilities[] = self::CALL;
            }

            if ($line->operator === 'cloudtalk') {
                // SMS needs the sender number of the line (see SmsObserver)
                if (!empty($config['numero'])) {
                    $capabilities[] = self::SMS;
                }

                // A call needs the API keys and the agent (see CloudTalk::makeCall)
                if (!empty($config['api_key_id']) && !empty($config['api_key_secret']) && !empty($config['agent_id'])) {
                    $capabilities[] = self::CALL;
                }
            }

            if (!$capabilities) {
                continue;
            }

            $providers[] = [
                'ref' => "line:{$line->id}",
                'label' => ucfirst($line->operator) . ' - ' . $line->name,
                'operator' => $line->operator,
                'capabilities' => $capabilities,
                'line_id' => (int) $line->id,
                'user_id' => $line->user_id ? (int) $line->user_id : null,
            ];
        }

        return $providers;
    }
}
