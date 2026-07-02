<?php

namespace App\Services;

class Ringover
{
	protected $apiUri;
	protected $apiVersion;
	protected $apiToken;

    /**
     * 
     * 
     */
    public function setup()
    {
        $this->apiUri = "https://public-api.ringover.com/";
        $this->apiVersion = "v2";
        $this->apiToken = config('ringover.api_token');

        return true;
    }

    /**
     * 
     * 
     */
    private function getBaseUrl($endpoint, $params = [])
    {
        return $this->apiUri . $this->apiVersion . "/" .
            $endpoint . (count($params) > 0 ? '?' : '') . 
            http_build_query($params);
    }

    /**
     * Show call
     */
    public function showCall($id)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "calls/$id" ;

        // URL
        $url = $this->getBaseUrl($endpoint);

        dd($url);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken
        ]);

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
        $result = json_decode($json, true);

        if (is_array($result) && isset($result['list']) && count($result['list']) > 0) {
            return $result['list'][0];
        }

        return null;
    }
}