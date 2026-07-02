<?php

namespace App\Services\Google;

class Calendar extends \Google\Client
{
    function __construct()
    {
        parent::__construct();

        $this->setClientId(config('google.client_id'));
        $this->setClientSecret(config('google.client_secret'));

        // Redirect to CalendarController::store
        $this->setRedirectUri(route('google.calendar.redirect'));

        // Calendar Scope
        $this->setScopes([
            \Google_Service_Oauth2::USERINFO_EMAIL,
            \Google_Service_Calendar::CALENDAR,
            \Google_Service_Calendar::CALENDAR_EVENTS,
        ]);

        $this->setApprovalPrompt('force');
        $this->setAccessType('offline');
        $this->setIncludeGrantedScopes(true);
    }
}