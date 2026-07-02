<?php

namespace App\Services;

class Pappers
{
	protected $apiUri;
	protected $apiToken;

    /**
     * 
     * 
     */
    public function setup()
    {
        $this->apiUri = "https://api.pappers.fr/";
        $this->apiToken = env('PAPPERS_TOKEN');

        return true;
    }

    /**
     * 
     * 
     */
    private function getBaseUrl($endpoint, $params = [], $version = 'v2')
    {
        return $this->apiUri . 
            $version . "/" . 
            $endpoint . '?' . 
            http_build_query(array_merge(['api_token' => $this->apiToken], $params));
    }

    /**
     * Get companies
     */
    public function getCompanies($params = [])
    {
        $this->setup();

        // Open a cURL session
        $ch = curl_init();

        $endpoint = "recherche";

        // URL
        $url = $this->getBaseUrl($endpoint, $params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
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

    /**
     * Show plot
     */
    public function showCompany($siren, $params = [])
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "entreprise";

        // URL
        $url = $this->getBaseUrl($endpoint, array_merge([
            'siren' => $siren
        ], $params));


        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
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