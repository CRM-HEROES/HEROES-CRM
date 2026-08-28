<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Security\RequestValidator;
use Twilio\TwiML\VoiceResponse;

class TwilioService
{
    protected const ACCESS_TOKEN_TTL_SECONDS = 3600;

    /**
     * Builds a short-lived Access Token (JWT) for the browser Voice SDK.
     * Never expose Account SID / Auth Token / API Key secret to the
     * browser directly — only this derived, scoped token.
     */
    public function issueAccessToken(string $identity): ?array
    {
        $accountSid = (string) config('services.twilio.account_sid');
        $apiKeySid = (string) config('services.twilio.api_key_sid');
        $apiKeySecret = (string) config('services.twilio.api_key_secret');
        $twimlAppSid = (string) config('services.twilio.twiml_app_sid');

        if ($accountSid === '' || $apiKeySid === '' || $apiKeySecret === '' || $twimlAppSid === '') {
            return null;
        }

        $token = new AccessToken(
            $accountSid,
            $apiKeySid,
            $apiKeySecret,
            self::ACCESS_TOKEN_TTL_SECONDS,
            $identity
        );

        $grant = new VoiceGrant();
        $grant->setOutgoingApplicationSid($twimlAppSid);
        $token->addGrant($grant);

        return [
            'token' => $token->toJWT(),
            'identity' => $identity,
        ];
    }

    /**
     * Validates the request came from Twilio. The signature is computed by
     * Twilio over the exact public URL it called; a request-derived URL is
     * unreliable behind a reverse proxy or an ngrok tunnel, so a fixed,
     * explicitly configured URL is used when available.
     */
    public function validateSignature(Request $request): bool
    {
        $authToken = (string) config('services.twilio.auth_token');
        if ($authToken === '') {
            return false;
        }

        $configuredUrl = (string) config('services.twilio.voice_webhook_url');
        $url = $configuredUrl !== '' ? $configuredUrl : $request->fullUrl();

        if ($configuredUrl === '') {
            Log::channel('twilio')->warning('TWILIO_VOICE_WEBHOOK_URL is not configured; falling back to the request-derived URL for signature validation, which is unreliable behind a proxy or tunnel.');
        }

        $signature = (string) $request->header('X-Twilio-Signature', '');
        if ($signature === '') {
            return false;
        }

        $validator = new RequestValidator($authToken);

        // Twilio signs the POST body params only, not the query string —
        // $request->request is Symfony's form-body bag, unlike input()/all()
        // which would also merge in query parameters.
        return $validator->validate($signature, $url, $request->request->all());
    }

    /**
     * TwiML served to Twilio's TwiML App when the browser SDK places an
     * outgoing call. Bridges the agent's browser leg to the destination.
     */
    public function voiceTwiml(?string $to): string
    {
        $response = new VoiceResponse();
        $callerId = (string) config('services.twilio.caller_id_number');
        $destination = $this->toE164($to);

        if (!$destination) {
            $response->say('Numéro de destination invalide.', ['language' => 'fr-FR']);

            return (string) $response;
        }

        // answerOnBridge keeps the browser leg ringing (rather than
        // connected to silence) until the destination actually picks up —
        // required for the JS SDK's Call "ringing" event to fire.
        $dialOptions = ['answerOnBridge' => true];
        if ($callerId !== '') {
            $dialOptions['callerId'] = $callerId;
        }
        $dial = $response->dial(null, $dialOptions);
        $dial->number($destination);

        return (string) $response;
    }

    /**
     * Kavkom's own normalizer only strips non-digits and never assumes a
     * country, since it can rely on the France-specific DID prefix. Twilio
     * requires a strict E.164 destination, so an explicit default country
     * (Belgium) is used for bare local numbers.
     */
    protected function toE164(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[\s.\-()]+/', '', $value);

        if (str_starts_with($value, '+')) {
            $number = $value;
        } elseif (str_starts_with($value, '00')) {
            $number = '+' . substr($value, 2);
        } else {
            $local = ltrim(preg_replace('/\D+/', '', $value), '0');
            $defaultCountryCode = (string) config('services.twilio.default_country_code', '32');
            $number = '+' . $defaultCountryCode . $local;
        }

        return preg_match('/^\+[1-9]\d{6,14}$/', $number) ? $number : null;
    }
}
