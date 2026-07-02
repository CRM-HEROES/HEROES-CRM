<?php

namespace App\Services;

class GouvFr
{
	protected $apiUri;

    /**
     * 
     * 
     */
    public function setup()
    {
        $this->apiUri = "https://recherche-entreprises.api.gouv.fr/";

        return true;
    }

    /**
     * 
     * 
     */
    private function getBaseUrl($endpoint, $params = [])
    {
        return $this->apiUri . 
            $endpoint . '?' . 
            http_build_query($params);
    }

    /**
     * Get companies
     */
    public function searchCompanies($params = [])
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "search";

        // URL
        $url = $this->getBaseUrl($endpoint, $params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "GET",
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