<?php

return [
    'api_uri' => env('BREVO_API_URI', 'https://api.brevo.com/v3/transactionalSMS/send'),
    'api_key' => env('BREVO_API_KEY'),
    'sender' => env('BREVO_SMS_SENDER'),
];