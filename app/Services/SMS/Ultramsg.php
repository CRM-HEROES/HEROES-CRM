<?php

namespace App\Services\SMS;

class Ultramsg
{
	protected $apiUri;
	protected $apiInstance;
	protected $apiToken;

	protected $options = [
		'method' => "GET",
		'endpoint' => '',
		'headers' => [],
		'params' => []
	];

    /**
     * 
     * 
     */
    public function setup()
    {
        // ULTRAMSG API URI
        $this->apiUri = config('ultramsg.api_uri');

        // ULTRAMSG API INSTANCE
        $this->apiInstance = config('ultramsg.instance');

        // ULTRAMSG API TOKEN
        $this->apiToken = config('ultramsg.token');

        return true;
    }

    /**
     * 
     * 
     */
    private function getBaseUrl()
    {
        return $this->apiUri . $this->apiInstance;
    }

    /**
     * 
     * 
     */
    private function checkInstanceConfigured()
    {
    	if (!$this->apiInstance) {
    		return false;
    	}

    	if (!$this->apiToken) {
    		return false;
    	}

		return true;
    }

    /**
     * Send a Whatsapp message
     * to a given number
     */
    public function sendSMS($number, $message, $priority = 1, $referenceId = "")
    {
        $this->setup();

    	// Check if token is well configured

    	if (!$this->checkInstanceConfigured()) {
    		throw new \Exception(trans("sms.ultramsg.error.empty_setting"));
    	}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->getBaseUrl() . "/messages/chat",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query([
				'token' => $this->apiToken,
				'to' => $number,
				'body' => $message,
				'priority' => $priority,
				'referenceId' => $referenceId
			]),
			CURLOPT_HTTPHEADER => array(
				"content-type: application/x-www-form-urlencoded"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			throw new \Exception($err);
		}

		if (empty($response)) {
			throw new \Exception(trans("sms.ultramsg.error.empty_response"));
		}

		$response = json_decode($response);

		if (isset($response->error)) {
			throw new \Exception($response->error);
		}

		return $response;
    }

    /**
     * 
     * 
     */
    public function instanceCheck()
    {
        $this->setup();
        
    	// Check if token is well configured
    	
    	if (!$this->checkInstanceConfigured()) {
    		throw new \Exception(trans("sms.ultramsg.error.empty_setting"));
    	}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->getBaseUrl() . "/instance/status?token=" . $this->apiToken,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"content-type: application/x-www-form-urlencoded"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			throw new \Exception($err);
		}

		if (empty($response)) {
			throw new \Exception(trans("sms.ultramsg.error.empty_response"));
		}

		$response = json_decode($response);

		if (isset($response->error)) {
			throw new \Exception($response->error);
		}

		return $response;
    }

    /**
     * 
     * 
     */
    public function numberCheck($number)
    {
        $this->setup();
        
    	// Check if token is well configured
    	
    	if (!$this->checkInstanceConfigured()) {
    		throw new \Exception(trans("sms.ultramsg.error.empty_setting"));
    	}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->getBaseUrl() . "/contacts/check?token=" . $this->apiToken . "&chatId=" . $number,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"content-type: application/x-www-form-urlencoded"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			throw new \Exception($err);
		}

		if (empty($response)) {
			throw new \Exception(trans("sms.ultramsg.error.empty_response"));
		}

		$response = json_decode($response);

		if (isset($response->error)) {
			throw new \Exception($response->error);
		}

		return $response;
    }
}