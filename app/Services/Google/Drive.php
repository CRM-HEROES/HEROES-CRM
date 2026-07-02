<?php

namespace App\Services\Google;

class Drive extends \Google\Client
{
    function __construct()
    {
        parent::__construct();

        $this->setClientId(config('google.client_id'));
        $this->setClientSecret(config('google.client_secret'));
        $this->setRedirectUri(route('google.drive.redirect'));
        $this->setScopes([
        	\Google_Service_Oauth2::USERINFO_EMAIL,
        	\Google_Service_Sheets::DRIVE, 
	    ]);
        $this->setApprovalPrompt('force');
        $this->setAccessType('offline');
        $this->setIncludeGrantedScopes(config('google.include_granted_scopes'));
    }
}