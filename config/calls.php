<?php

return [
    'enabled' => (bool) env('CALLS_ENABLED', false),
    'provider' => env('CALLS_PROVIDER', 'fake'),
    'webhook_secret' => env('CLOUDTALK_WEBHOOK_SECRET'),
    'ai_voice' => [
        'webhook_secret' => env('CALLS_AI_VOICE_WEBHOOK_SECRET'),
    ],
    'work_hours' => [
        'start' => '08:00',
        'end' => '17:00',
    ],
    'work_days' => [1, 2, 3, 4, 5],
    'holidays' => [],
    'max_attempts' => 3,
    'callback_delay_hours' => 48,
    'sms_frequency_hours' => 48,
    'max_daily_calls_per_project' => 100,
    'fake_outcome' => 'no_answer',
];
