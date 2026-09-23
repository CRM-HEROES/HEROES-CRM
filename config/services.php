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

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'live_model' => env('GEMINI_LIVE_MODEL', 'models/gemini-2.5-flash-native-audio-preview-09-2025'),
        'api_version' => env('GEMINI_API_VERSION', 'v1alpha'),
    ],

    'ai_phone_agent' => [
        'secret'     => env('AI_PHONE_AGENT_SHARED_SECRET'),
        'bridge_url' => env('AI_PHONE_AGENT_BRIDGE_URL'),

        // --- Static / demo agent (used when no AiAgent row exists in DB) ---
        // Set these in .env to run the full AI-call flow without back-office setup.
        'demo_agent_name'         => env('AI_PHONE_AGENT_DEMO_AGENT_NAME', 'Assistant IA Heroes CRM'),
        'demo_agent_script'       => env('AI_PHONE_AGENT_DEMO_SCRIPT', ''),
        'demo_agent_instructions' => env('AI_PHONE_AGENT_DEMO_INSTRUCTIONS', ''),

        // Kavkom SIP credentials for the agent leg (the AI channel that joins
        // the 3-way FreeSWITCH conference and bridges audio to Gemini Live).
        'demo_kavkom_extension'   => env('AI_PHONE_AGENT_DEMO_KAVKOM_EXTENSION'),
        'demo_kavkom_password'    => env('AI_PHONE_AGENT_DEMO_KAVKOM_PASSWORD'),
        'demo_kavkom_context'     => env('AI_PHONE_AGENT_DEMO_KAVKOM_CONTEXT'),
        'demo_kavkom_transport'   => env('AI_PHONE_AGENT_DEMO_KAVKOM_TRANSPORT', 'tls'),
        'demo_kavkom_sip_port'    => env('AI_PHONE_AGENT_DEMO_KAVKOM_SIP_PORT', 5061),

        // Fallback extension for the CRM user (conseiller) when their personal
        // Kavkom settings are not yet configured in the interface.
        'demo_user_extension'     => env('AI_PHONE_AGENT_DEMO_USER_EXTENSION'),
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
