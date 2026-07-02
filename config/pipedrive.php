<?php

return [
    /*
    |----------------------------------------------------------------------------
    | Pipedrive
    |----------------------------------------------------------------------------
    */
    'api_uri' => env('PIPEDRIVE_API_URI', "https://api.pipedrive.com/"),
    'api_version' => env('PIPEDRIVE_API_VERSION', "v1"),
    'api_token' => env('PIPEDRIVE_API_TOKEN', '')
];
