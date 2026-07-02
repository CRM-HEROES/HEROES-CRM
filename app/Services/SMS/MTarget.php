<?php

namespace App\Services\SMS;

use Illuminate\Support\Str;

class MTarget
{
	protected $apiUri;
	protected $username;
	protected $password;

	protected $options = [
		'method' => "POST",
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
        // API URI
        $this->apiUri = config('mtarget.api_uri');

        $this->username = config('mtarget.username');

        $this->password = config('mtarget.password');

        return true;
    }

    /**
     * 
     * 
     */
    private function checkInstanceConfigured()
    {
    	if (!$this->username) {
    		return false;
    	}

    	if (!$this->password) {
    		return false;
    	}

		return true;
    }

    /**
     * Send a MTarget message
     * to a given number
     */
    public function sendSMS($number, $message)
    {
        $this->setup();

    	// Check if token is well configured

    	if (!$this->checkInstanceConfigured()) {
    		throw new \Exception(trans("sms.mtarget.error.empty_setting"));
    	}

        if (Str::startsWith($number, '06')) {
            $number = "+33" . substr($number, 1);
            $number = Str::replace(' ', '', $number);
        }

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->apiUri . "/messages",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query([
				'username' => $this->username,
				'password' => $this->password,
				'msisdn' => $number,
				'msg' => $message
			]),
			CURLOPT_HTTPHEADER => array(
				"content-type: application/x-www-form-urlencoded"
			),
            CURLOPT_VERBOSE => TRUE,
            CURLOPT_STDERR => $verbose = fopen('php://temp', 'rw+'),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			throw new \Exception($err);
		}

		if (empty($response)) {
			throw new \Exception(trans("sms.mtarget.error.empty_response"));
		}

		$response = json_decode($response);

		if (!isset($response->results) || empty($response->results)) {
			throw new \Exception(trans("sms.mtarget.error.empty_response"));
		}

		if ($response->results[0]->reason !== "ACCEPTED") {
			throw new \Exception("MTarget: " . $response->results[0]->reason);
		}

		return $response;
    }
}