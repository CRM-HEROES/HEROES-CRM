<?php

namespace App\Services;

use App\Models\Project;
use App\Utils\ProjectSetting;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;

class Docusign
{
	/** hold config value */
	protected $config;

    protected $project;
	protected $setting;

    function __construct()
    {
    }

    /**
     * 
     * 
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        if (!$this->project) {
        	return false;
        }

        $this->setting = (array) ProjectSetting::get($project, 'docusign');

        return $this->setting;
    }

    /**
     * 
     * 
     */
    public function connect($callbackUrl)
    {
        $params = [
            'response_type' => 'code',
            'scope' => 'signature',
            'client_id' => $this->setting['client_id'],
            'state' => 'a39fh23hnf23',
            'redirect_uri' => $callbackUrl,
        ];
        $queryBuild = http_build_query($params);

        $url = env('DOCUSIGN_AUTH_BASE_URL') . "/oauth/auth?";

        $botUrl = $url . $queryBuild;

        return redirect()->to($botUrl);
    }

    /**
     * 
     * 
     */
    public function storeToken($code)
    {
        if (!$this->project) {
            return null;
        }

        $clientId = $this->setting['client_id'];
        $clientSecret = $this->setting['client_secret'];

        $integratorAndSecretKey = "Basic " . utf8_decode(base64_encode("{$clientId}:{$clientSecret}"));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, env('DOCUSIGN_AUTH_BASE_URL') . '/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $post = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $headers = array();
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = "authorization: $integratorAndSecretKey";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        $token = json_decode($result);

        $this->updateToken($token);

        return $token;
    }

    /**
     * 
     * 
     */
    private function updateToken($token)
    {
        if (!$this->project) {
            return null;
        }

        $docusign_settings = $this->setting;
        $docusign_settings['token_at'] = strval(\Carbon\Carbon::now());
        $docusign_settings['token'] = $token;
        ProjectSetting::set($this->project, 'docusign', $docusign_settings);

        return $token;
    }

    /**
     * 
     * 
     */
    public function checkToken()
    {
        if (
            !$this->project ||
            !$this->setting ||
            !isset($this->setting['token']) ||
            !isset($this->setting['token']['access_token'])
        ) {
            throw new \Exception("Aucune configuration Docusign n'a été définie pour ce projet, veuillez contacter votre administrateur.");
        }

        if (
            // check when the token was stored
            !isset($this->setting['token_at']) ||

            // check token expires in
            !isset($this->setting['token']['expires_in']) ||

            // check if (now - token_at <= expires_in)
            // if not, refresh token using refresh_token value
            \Carbon\Carbon::now()->diffInSeconds(\Carbon\Carbon::parse($this->setting['token_at'])) > $this->setting['token']['expires_in']
        ) {
            return $this->refreshToken();
        }

        // return actual token
        return $this->setting['token'];
    }

    /**
     * 
     * 
     */
    private function refreshToken()
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => env('DOCUSIGN_AUTH_BASE_URL') . "/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => [
                'grant_type' => 'refresh_token',
                'client_id' => $this->setting['client_id'],
                'client_secret' => $this->setting['client_secret'],
                'refresh_token' => $this->setting['token']['refresh_token']
            ]
        ]);

        // Send the cURL request and decode the response
        $response = curl_exec($curl);

        // Close the cURL connection
        curl_close($curl);

        $response = json_decode($response);

        return $this->updateToken($response);
    }

    /**
     * 
     * 
     */
    public function signDocument($token, $callbackUrl, $document, $signer, $signHere = null)
    {
        $this->checkToken();

        // Envelope API
        $config = new Configuration();
        $config->setHost(env('DOCUSIGN_BASE_URL') . "/restapi");
        $config->addDefaultHeader('Authorization', 'Bearer ' . $token);    
        $apiClient = new ApiClient($config);
        $envelopeApi = new EnvelopesApi($apiClient);

        // Get envelope Id
        $envelopeDefinition = $this->makeEnvelope($document, $signer, $signHere);
        $results = $envelopeApi->createEnvelope($this->setting['account_id'], $envelopeDefinition);
        $envelopeId = $results->getEnvelopeId();

        // Recipient view request
        $recipientViewRequest = new \DocuSign\eSign\Model\RecipientViewRequest([
            'authentication_method' => 'None',
            'client_user_id' => $signer['client_user_id'],
            'recipient_id' => '1',
            'return_url' => $callbackUrl,
            'user_name' => $signer['name'], 
            'email' => $signer['email']
        ]);

        $results = $envelopeApi->createRecipientView($this->setting['account_id'], $envelopeId, $recipientViewRequest);

        return [
            'envelope_id' => $envelopeId,
            'url' => $results['url']
        ];
	}

    private function makeEnvelope($document, $signer, $signHere = null, $emailSubject = "Veuillez signer le document suivant.")
    {
        $arrContextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];  

        $content_bytes = file_get_contents($document['path'], false, stream_context_create($arrContextOptions));
        $base64_file_content = base64_encode($content_bytes);

        // Create the document model
        $pathinfo = pathinfo($document['path']);
        $document = new \DocuSign\eSign\Model\Document([# create the DocuSign document object
        	'document_base64' => $base64_file_content,
            'name' => isset($document['name']) ? $document['name'] : $pathinfo['basename'], # can be different from actual file name
            'file_extension' => $pathinfo['extension'], # many different document types are accepted
            'document_id' => isset($document['id']) ? $document['id'] : 1, # a label used to reference the doc
        ]);

        // Create the signer recipient model
        $signer = new \DocuSign\eSign\Model\Signer($signer);

        // Create a sign_here tab (field on the document)
        if ($signHere) {
	        $sign_here = new \DocuSign\eSign\Model\SignHere($signHere);
	        # Add the tabs model (including the sign_here tab) to the signer
	        # The Tabs object wants arrays of the different field/tab types
	        $signer->settabs(new \DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$sign_here]]));
        }


        # Next, create the top level envelope definition and populate it.
        $envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition([
            'email_subject' => $emailSubject,
            'documents' => [$document],
            # The Recipients object wants arrays for each recipient type
            'recipients' => new \DocuSign\eSign\Model\Recipients(['signers' => [$signer]]),
            'status' => "sent", # requests that the envelope be created and sent.
        ]);

        return $envelope_definition;
    }

    public function getDocuments($token, $envelopeId)
    {
        $this->checkToken();
        
        $endpoint_url = env('DOCUSIGN_BASE_URL') . "/restapi/v2.1/accounts/{$this->setting['account_id']}/envelopes/$envelopeId/documents";

        // set up authentication headers
        $headers = array(
            "Authorization: Bearer $token"
        );

        // make the GET request to the endpoint URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // find the document you want to download (e.g. by document name or ID)
        $documents = json_decode($response, true);
        
        return $documents["envelopeDocuments"];
    }

    public function downloadFile($token, $envelopeId, $documentId)
    {
        $this->checkToken();
        
        $endpoint_url = env('DOCUSIGN_BASE_URL') . "/restapi/v2.1/accounts/{$this->setting['account_id']}/envelopes/$envelopeId/documents/$documentId";

        // set up authentication headers
        $headers = array(
            "Authorization: Bearer $token",
            "Content-Type: application/pdf" // set the file format you want to download
        );

        // make the GET request to the endpoint URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function downloadFirstFile($token, $envelopeId)
    {
        $this->checkToken();
        
        foreach ($this->getDocuments($token, $envelopeId) as $document) {
            $firstDocument = $document;
            break;
        }

        if (!$firstDocument) {
            die("Document not found.");
        }

        return $this->downloadFile($token, $envelopeId, $firstDocument['documentId']);
    }

}