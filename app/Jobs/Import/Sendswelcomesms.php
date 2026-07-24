<?php

namespace App\Jobs\Import;

use App\Models\Import;
use App\Models\Sms;
use App\Services\SMS\Brevo;
use App\Services\SMS\SMSBOX;
use App\Utils\Field\Renderer\ProjectFieldRenderer;
use App\Utils\Field\Renderer\ProspectFieldRenderer;
use App\Utils\ProjectSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait SendsWelcomeSms
{
    /**
     * Send the welcome SMS to every prospect still
     * attached to the given import, once import
     * processing (and duplicate check) is done.
     *
     * Guarded by `welcome_sms_sent_at` so it is only
     * ever sent once per import, even if this is called
     * from more than one place (eg. once at the end of
     * ImportProspects, and again if the user later runs
     * an additional manual duplicate resolution pass).
     */
    protected function sendWelcomeSms(Import $import)
    {
        if (!$import->notify_welcome_sms) {
            return;
        }

        if (empty($import->welcome_sms_message)) {
            return;
        }

        // Reload to avoid acting on a stale
        // in-memory copy of the import
        if ($import->refresh()->welcome_sms_sent_at) {
            return;
        }

        $source = $import->welcome_sms_source ?: 'brevo';

        $query = $import
            ->prospects()
            ->whereNotNull('mobile_phone_number')
            ->where('mobile_phone_number', '!=', '');

        // Batches of 50 concurrent requests at a time: enough to get
        // a big speedup without overwhelming the provider's API
        // (rate limits) or opening too many sockets at once.
        $query->chunk(50, function ($prospects) use ($import, $source) {
            switch ($source) {
                case 'smsbox':
                    $this->sendWelcomeSmsBySmsboxPool($import, $prospects);
                    break;

                case 'ultramsg':
                    $this->sendWelcomeSmsByUltramsgPool($import, $prospects);
                    break;

                case 'mtarget':
                    $this->sendWelcomeSmsByMTargetPool($import, $prospects);
                    break;

                case 'brevo':
                default:
                    $this->sendWelcomeSmsByBrevoPool($import, $prospects);
                    break;
            }
        });

        $import->update([
            'welcome_sms_sent_at' => now(),
        ]);
    }

    // POOLS PER PROVIDER

    /**
     * Brevo - concurrent send via Http::pool().
     * Mirrors App\Services\SMS\Brevo::sendSMS().
     */
    protected function sendWelcomeSmsByBrevoPool(Import $import, $prospects)
    {
        $project = $import->project;
        $setting = ProjectSetting::get($project, 'brevo');

        if (!$setting || empty($setting->api_key) || empty($setting->sender)) {
            $this->logProviderError($project, 'Brevo: ' . trans('sms.brevo.error.empty_setting'));
            return;
        }

        $apiUri = config('brevo.api_uri', 'https://api.brevo.com/v3/transactionalSMS/send');
        $messages = $this->personalizeMessages($import, $prospects);

        $responses = Http::pool(function ($pool) use ($prospects, $messages, $setting, $apiUri) {
            foreach ($prospects as $prospect) {
                $recipient = preg_replace('/[^0-9]/', '', $prospect->mobile_phone_number);

                $pool
                    ->as((string) $prospect->id)
                    ->withHeaders([
                        'api-key' => $setting->api_key,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ])
                    ->post($apiUri, [
                        'sender' => $setting->sender,
                        'recipient' => $recipient,
                        'content' => $messages[$prospect->id],
                        'type' => Brevo::TYPE_TRANSACTIONAL,
                    ]);
            }
        });

        $this->recordResults($import, $prospects, $messages, 'brevo', function ($prospect) use ($responses) {
            $response = $responses[(string) $prospect->id] ?? null;

            if (!$response) {
                return trans('sms.brevo.error.other');
            }

            if ($response->successful()) {
                return null;
            }

            return $response->json('message', $response->body()) . " : " . trans('sms.brevo.error.other');
        });
    }

    /**
     * SMSBOX - concurrent send via Http::pool().
     * Mirrors App\Services\SMS\SMSBOX::sendSMS() (plain GET,
     * response is a raw "OK[ id]" or "ERROR 0X" text body).
     */
    protected function sendWelcomeSmsBySmsboxPool(Import $import, $prospects)
    {
        $project = $import->project;
        $settings = ProjectSetting::get($project, 'smsbox');

        $apiUri = data_get($settings, 'api_uri', 'https://api.smsbox.pro/1.1/api.php');
        $apiKey = data_get($settings, 'api_key');

        if (empty($apiUri) || empty($apiKey)) {
            $this->logProviderError($project, 'SMSBOX: ' . trans('sms.smsbox.error.empty_setting'));
            return;
        }

        $messages = $this->personalizeMessages($import, $prospects);

        // Same defaults as SMSBOX::setup()
        $baseParams = [
            'apikey' => $apiKey,
            'mode' => SMSBOX::MODE_RESPONSE,
            'strategy' => SMSBOX::STRATEGY_PRIVATE,
            'charset' => SMSBOX::CHARSET_UTF_8,
            'id' => 1,
            'callback' => 1,
        ];

        $responses = Http::pool(function ($pool) use ($prospects, $messages, $apiUri, $baseParams) {
            foreach ($prospects as $prospect) {
                $data = $baseParams;
                $data['msg'] = $messages[$prospect->id];
                $data['dest'] = $prospect->mobile_phone_number;

                $pool->as((string) $prospect->id)
                    ->withOptions(['verify' => false])
                    ->get($apiUri, $data);
            }
        });

        $this->recordResults($import, $prospects, $messages, 'smsbox', function ($prospect) use ($responses) {
            $response = $responses[(string) $prospect->id] ?? null;

            if (!$response) {
                return trans('sms.smsbox.error.other');
            }

            $body = trim($response->body());

            if (substr($body, 0, 2) === 'OK') {
                return null;
            }

            return $body !== '' ? $body . " : " . trans('sms.smsbox.error.other') : trans('sms.smsbox.error.other');
        });
    }

    /**
     * Ultramsg - concurrent send via Http::pool().
     * Mirrors App\Services\SMS\Ultramsg::sendSMS()
     * (form-urlencoded POST to {apiUri}{instance}/messages/chat).
     */
    protected function sendWelcomeSmsByUltramsgPool(Import $import, $prospects)
    {
        $project = $import->project;
        $settings = ProjectSetting::get($project, 'ultramsg');

        $instance = data_get($settings, 'instance');
        $token = data_get($settings, 'token');

        // NOTE: mirrors an existing quirk in App\Jobs\SMS\UltraMsg::setSettings(),
        // which reads the "api_uri" config from the "api_key" settings key
        // (in practice always falls back to the default below since
        // "api_key" isn't a real Ultramsg setting field).
        $apiUri = data_get($settings, 'api_key', 'https://api.ultramsg.com/');

        if (empty($instance) || empty($token)) {
            $this->logProviderError($project, 'Ultramsg: ' . trans('sms.ultramsg.error.empty_setting'));
            return;
        }

        $messages = $this->personalizeMessages($import, $prospects);
        $url = $apiUri . $instance . '/messages/chat';

        $responses = Http::pool(function ($pool) use ($prospects, $messages, $token, $url) {
            foreach ($prospects as $prospect) {
                $pool
                    ->as((string) $prospect->id)
                    ->withOptions(['verify' => false])
                    ->asForm()
                    ->post($url, [
                        'token' => $token,
                        'to' => $prospect->mobile_phone_number,
                        'body' => $messages[$prospect->id],
                        'priority' => 1,
                        'referenceId' => '',
                    ]);
            }
        });

        $this->recordResults($import, $prospects, $messages, 'ultramsg', function ($prospect) use ($responses) {
            $response = $responses[(string) $prospect->id] ?? null;

            if (!$response || empty($response->body())) {
                return trans('sms.ultramsg.error.empty_response');
            }

            $body = $response->json();

            if (isset($body['error'])) {
                return $body['error'];
            }

            if ($response->failed()) {
                return trans('sms.ultramsg.error.other');
            }

            return null;
        });
    }

    /**
     * MTarget - concurrent send via Http::pool().
     * Mirrors App\Services\SMS\MTarget::sendSMS()
     * (form-urlencoded POST to {apiUri}/messages).
     */
    protected function sendWelcomeSmsByMTargetPool(Import $import, $prospects)
    {
        $project = $import->project;
        $settings = ProjectSetting::get($project, 'mtarget');

        $apiUri = data_get($settings, 'api_uri', 'https://api-public-2.mtarget.fr');
        $username = data_get($settings, 'username');
        $password = data_get($settings, 'password');

        if (empty($username) || empty($password)) {
            $this->logProviderError($project, 'MTarget: ' . trans('sms.mtarget.error.empty_setting'));
            return;
        }

        $messages = $this->personalizeMessages($import, $prospects);
        $url = $apiUri . '/messages';

        $responses = Http::pool(function ($pool) use ($prospects, $messages, $username, $password, $url) {
            foreach ($prospects as $prospect) {
                $number = $prospect->mobile_phone_number;

                // Same "06..." -> "+336..." normalization
                // as MTarget::sendSMS()
                if (Str::startsWith($number, '06')) {
                    $number = Str::replace(' ', '', '+33' . substr($number, 1));
                }

                $pool
                    ->as((string) $prospect->id)
                    ->withOptions(['verify' => false])
                    ->asForm()
                    ->post($url, [
                        'username' => $username,
                        'password' => $password,
                        'msisdn' => $number,
                        'msg' => $messages[$prospect->id],
                    ]);
            }
        });

        $this->recordResults($import, $prospects, $messages, 'mtarget', function ($prospect) use ($responses) {
            $response = $responses[(string) $prospect->id] ?? null;

            if (!$response || empty($response->body())) {
                return trans('sms.mtarget.error.empty_response');
            }

            $body = $response->json();

            if (empty($body['results'])) {
                return trans('sms.mtarget.error.empty_response');
            }

            if (($body['results'][0]['reason'] ?? null) !== 'ACCEPTED') {
                return 'MTarget: ' . ($body['results'][0]['reason'] ?? trans('sms.mtarget.error.other'));
            }

            return null;
        });
    }

    // SHARED HELPERS

    /**
     * Personalize the welcome message per prospect,
     * using the same field renderers as
     * SmsObserver::computeMessage().
     */
    protected function personalizeMessages(Import $import, $prospects)
    {
        $project = $import->project;
        $projectFieldRenderer = new ProjectFieldRenderer($project);
        $messages = [];

        foreach ($prospects as $prospect) {
            $message = (new ProspectFieldRenderer($project, $prospect))
                ->render($import->welcome_sms_message);

            $messages[$prospect->id] = $projectFieldRenderer->render($message);
        }

        return $messages;
    }

    /**
     * Create the Sms record for each prospect of the batch,
     * with the result of its (already sent) HTTP request,
     * bypassing Sms::create()'s normal event flow
     * (SmsObserver) since sending already happened via the
     * pool above - going through the observer again would
     * trigger a second, duplicate send attempt.
     *
     * $resolveError is called once per prospect and must
     * return null (success) or an error message (failure).
     */
    protected function recordResults(Import $import, $prospects, $messages, $source, callable $resolveError)
    {
        Sms::withoutEvents(function () use ($prospects, $messages, $source, $import, $resolveError) {
            foreach ($prospects as $prospect) {
                $error = $resolveError($prospect);

                $prospect->sms()->create([
                    'message' => $messages[$prospect->id],
                    'source' => $source,
                    'from_user' => 1,
                    'creator_id' => $import->creator_id,
                    'error' => $error,
                    'sent_at' => $error ? null : now(),
                ]);
            }
        });
    }

    /**
     * Log a provider configuration error the same way
     * the existing SMS jobs do (App\Jobs\SMS\*).
     */
    protected function logProviderError($project, $message)
    {
        if (class_exists(\ProjectLog::class)) {
            \ProjectLog::error($project, $message);
        }
    }
}