<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Campaign timezone
    |--------------------------------------------------------------------------
    |
    | The campaign "date" and "hour" are stored as naive local date times (the
    | value typed in the campaign card). The scheduler compares them with the
    | current time expressed in this timezone, so it must be the timezone the
    | users have in mind when they pick an hour (e.g. Europe/Paris).
    | Defaults to the application timezone.
    |
    */

    'timezone' => env('CAMPAIGN_TIMEZONE', config('app.timezone', 'UTC')),

];
