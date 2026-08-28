<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'transcription_model' => env('OPENAI_TRANSCRIPTION_MODEL', 'whisper-1'),
    ],

    // Kavkom sends the post-call CDR to our public endpoint. This is a
    // separate secret configured as a custom header in Kavkom; an API token
    // must never be used as a webhook secret.
    'kavkom' => [
        'webhook_secret' => env('KAVKOM_WEBHOOK_SECRET'),
        // Optional base URL used only when the CDR mapping supplies a
        // recording path rather than a complete HTTPS URL.
        'recording_base_url' => env('KAVKOM_RECORDING_BASE_URL'),
    ],

    // Twilio Voice JS SDK ("Téléphone dans le navigateur") — compte unique
    // partagé par toute l'équipe, pas par utilisateur comme Kavkom. Ajouté
    // comme opérateur de secours quand Kavkom est peu fiable sur certaines
    // destinations (ex. Belgique) ; Kavkom reste l'opérateur par défaut.
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'api_key_sid' => env('TWILIO_API_KEY_SID'),
        'api_key_secret' => env('TWILIO_API_KEY_SECRET'),
        'twiml_app_sid' => env('TWILIO_TWIML_APP_SID'),
        'caller_id_number' => env('TWILIO_CALLER_ID_NUMBER'),
        // URL publique fixe configurée comme "Voice Request URL" de la
        // TwiML App Twilio. Utilisée pour valider X-Twilio-Signature au
        // lieu de l'URL déduite de la requête, peu fiable derrière un
        // reverse proxy ou un tunnel ngrok en local.
        'voice_webhook_url' => env('TWILIO_VOICE_WEBHOOK_URL'),
        'default_country_code' => env('TWILIO_DEFAULT_COUNTRY_CODE', '32'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'live_model' => env('GEMINI_LIVE_MODEL', 'models/gemini-2.5-flash-native-audio-preview-09-2025'),
        'api_version' => env('GEMINI_API_VERSION', 'v1alpha'),
    ],

    'ai_phone_agent' => [
        'secret' => env('AI_PHONE_AGENT_SHARED_SECRET'),
        'bridge_url' => env('AI_PHONE_AGENT_BRIDGE_URL'),
    ],

    'ai_quote' => [
        'trigger_label' => env('AI_QUOTE_TRIGGER_LABEL', 'Bilan réalisé'),
        'document_template_name' => env('AI_QUOTE_DOCUMENT_TEMPLATE', 'Devis IA'),
    ],

    // ARCHER nightly enrichment (P6): contact verification + LinkedIn lookup.
    'dropcontact' => [
        'key' => env('DROPCONTACT_API_KEY'),
        'base_url' => env('DROPCONTACT_BASE_URL', 'https://api.dropcontact.com'),
    ],

    // NinjaPear: what Proxycurl became after being sunset (LinkedIn lawsuit,
    // 2025) — same nubela.co account/API key, no LinkedIn/social scraping.
    'ninjapear' => [
        'key' => env('NINJAPEAR_API_KEY'),
        'base_url' => env('NINJAPEAR_BASE_URL', 'https://nubela.co'),
    ],

];
