<?php

namespace App\Services;

class Mindee
{
	protected $apiUri;
	protected $apiAccount;
	protected $apiVersion;
	protected $apiEndpoint;
	protected $apiKey;

    /**
     * 
     * 
     */
    public function setup()
    {
        $this->apiUri = config('mindee.api_uri');
        $this->apiAccount = config('mindee.api_account');
        $this->apiVersion = config('mindee.api_version');
        $this->apiEndpoint = config('mindee.api_endpoint');
        $this->apiKey = config('mindee.api_key');

        return true;
    }

    /**
     * 
     * 
     */
    private function getBaseUrl($endpoint)
    {
        return $this->apiUri . $this->apiAccount . "/" . $endpoint . "/v" . $this->apiVersion . "/predict";
    }

    /**
     * 
     */
    public function predict($endpoint, $file, $mime)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        // Setup headers
        $headers = array(
            "Authorization: Token " . $this->apiKey
        );

        // Add our file to the request
        $data = array(
            "document" => new \CURLFile(
                $file,
                $mime,
                substr($file, strrpos($file, "/") + 1)
            )
        );

        // URL for a prediction
        $url = $this->getBaseUrl($endpoint);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        );

        // Set all options for the cURL request
        curl_setopt_array(
            $ch,
            $options
        );

        // Execute the request & extract the query content into a variable
        $json = curl_exec($ch);

        // Close the cURL session
        curl_close($ch);

        // Store the response as an array to allow for easier manipulations
        return json_decode($json, true);
    }
}