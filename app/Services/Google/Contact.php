<?php

namespace App\Services\Google;

class Contact extends \Google\Client
{
    function __construct()
    {
        parent::__construct();

        $this->setClientId(config('google.client_id'));
        $this->setClientSecret(config('google.client_secret'));

        // Redirect to ContactController::store
        $this->setRedirectUri(route('google.contact.redirect'));

        // Calendar Scope
        $this->setScopes([
            \Google_Service_Oauth2::USERINFO_EMAIL,
            \Google_Service_PeopleService::CONTACTS
        ]);

        $this->setApprovalPrompt('force');
        $this->setAccessType('offline');
        $this->setIncludeGrantedScopes(true);
    }
}