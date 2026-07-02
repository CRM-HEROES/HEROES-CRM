<?php

return [
    'api_key'   => env('GOOGLE_MAP_API_KEY', null),
    'output'    => env('GOOGLE_MAP_API_OUTPUT', "json"),
    'language'  => env('GOOGLE_MAP_API_LANGUAGE', "fr"),
    'inputtype' => env('GOOGLE_MAP_API_INPUT_TYPE', "textquery"),
    'fields'    => env('GOOGLE_MAP_API_FIELDS', "formatted_address,place_id,geometry")
];
