<?php

namespace App\Services;

class Pipedrive
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
        $this->apiUri = config('pipedrive.api_uri');
        $this->apiVersion = config('pipedrive.api_version');
        $this->apiToken = config('pipedrive.api_token');

        return true;
    }

    /**
     * 
     * 
     */
    private function getBaseUrl($endpoint, $params = [], $version = 'v1')
    {
        return $this->apiUri . 
            $version . "/" . 
            $endpoint . '?' . 
            http_build_query(array_merge(['api_token' => $this->apiToken], $params));
    }

    /**
     * Get persons
     */
    public function getPersons($params = [])
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "persons";

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
    
    /**
     * Show person
     */
    public function showPerson($id)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "persons/{$id}";

        // URL
        $url = $this->getBaseUrl($endpoint);

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
    
    /**
     * 
     */
    public function storePerson($params)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "persons";

        // URL
        $url = $this->getBaseUrl($endpoint);

        $params = json_encode($params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)
            ],
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
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
    
    /**
     * 
     */
    public function updatePerson($id, $params)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "persons/{$id}";

        // URL
        $url = $this->getBaseUrl($endpoint);

        $params = json_encode($params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)
            ],
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_POSTFIELDS     => $params,
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
    
    /**
     * Get webhooks
     */
    public function getWebhooks($params = [])
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "webhooks";

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
    
    /**
     * 
     */
    public function storeWebhook($params)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "webhooks";

        // URL
        $url = $this->getBaseUrl($endpoint);

        $params = json_encode($params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)
            ],
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
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
    
    /**
     * Get deals
     */
    public function getDeals($params = [])
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "deals";

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
    
    /**
     * Get person deals
     */
    public function getPersonDeals($personId, $params = [])
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "persons/{$personId}/deals";

        // URL
        $url = $this->getBaseUrl($endpoint, array_merge($params, ['person_id' => $personId]));

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
    
    /**
     * 
     */
    public function storeDeal($params)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "deals";

        // URL
        $url = $this->getBaseUrl($endpoint);

        $params = json_encode($params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)
            ],
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
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
    
    /**
     * 
     */
    public function updateDeal($id, $params)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "deals/{$id}";

        // URL
        $url = $this->getBaseUrl($endpoint);

        $params = json_encode($params);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params)
            ],
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_POSTFIELDS     => $params,
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
    
    /**
     * Get deals
     */
    public function getDealProducts($dealId)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "/deals/$dealId/products";

        // URL
        $url = $this->getBaseUrl($endpoint);

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
    
    /**
     * Get deals
     */
    public function getDealNotes($dealId)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "/notes";

        // URL
        $url = $this->getBaseUrl($endpoint, [
            'deal_id' => $dealId
        ]);

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
    
    /**
     * Stage show
     */
    public function showStage($id)
    {
        $this->setup();

        // Open a cURL session to send the document
        $ch = curl_init();

        $endpoint = "stages/{$id}";

        // URL
        $url = $this->getBaseUrl($endpoint);

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