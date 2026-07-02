<?php

namespace App\Services\Google;

use App\Events\Google\MapRequestSent;

class Map
{
    protected $endPoint;
    protected $apiKey;
    protected $options;

    function __construct()
    {
        $this->endPoint = env('GOOGLE_MAP_API_ENDPOINT', "https://maps.googleapis.com/maps/api");
    }

    /**
     * 
     * 
     */
    public function setup()
    {
        $this->apiKey = config('google-map.api_key');
        $this->options = [
            'output'    => config('google-map.output'),
            'language'  => config('google-map.language'),
            'inputtype' => config('google-map.inputtype'),
            'fields'    => config('google-map.fields')
        ];
    }

    private function sendCurl($url)
    {
        if (!$this->apiKey) {
            throw new \Exception("You should configure an API key for your application.");
        }

        $ch = curl_init();

        // Endpoint URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // Request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        // Other options
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Send cURL
        $output = curl_exec($ch);

        // close cURL
        curl_close($ch);

        MapRequestSent::dispatch();

        return $output;
    }

    public function find($place)
    {
        $this->setup();

        $url = $this->endPoint . "/place/findplacefromtext/";
        $url .= $this->options['output'] . "?";

        $url .= "key="  . $this->apiKey;
        $url .= "&input="  . urlencode($place);
        $url .= "&language="  . $this->options['language'];
        $url .= "&inputtype=" . $this->options['inputtype'];
        $url .= "&fields="    . $this->options['fields'];

        return json_decode($this->sendCurl($url), true);
    }

    public function nearBySearch($lat, $lng, $radius, $place, $token = null)
    {
        $this->setup();

        $url = $this->endPoint . "/place/nearbysearch/";
        $url .= $this->options['output'] . "?";

        $url .= "key="  . $this->apiKey;
        $url .= "&location="  . $lat . "," . $lng;
        $url .= "&radius="  . $radius;
        $url .= "&keyword=" . $place;
        $url .= "&pagetoken=" . $token;

        return json_decode($this->sendCurl($url), true);
    }

    public function details($placeId)
    {
        $this->setup();

        $url = $this->endPoint . "/place/details/";
        $url .= $this->options['output'] . "?";

        $url .= "key="  . $this->apiKey;
        $url .= "&place_id="  . urlencode($placeId);

        return json_decode($this->sendCurl($url), true);
    }

    public function direction($origin, $destination, $waypoints = null)
    {
        $this->setup();
        
        $url = $this->endPoint . "/directions/";
        $url .= $this->options['output'] . "?";

        $requests = [
            'language' => $this->options['language'],
            'origin' => $origin,
            'destination' => $destination,
            'travelMode' => "DRIVING",
            'optimizeWaypoints' => false,
            'waypoints' => $waypoints ? array_map(function($wp) {
                return [
                    'location' => ['query' => $wp],
                    'stopover' => true
                ];
            }, $waypoints) : [],
        ];

        $url .= "key="  . $this->apiKey;
        $url .= "&language="  . $requests['language'];
        $url .= "&origin="  . urlencode($requests['origin']);
        $url .= "&destination="  . urlencode($requests['destination']);
        $url .= "&travelMode="  . $requests['travelMode'];

        if (!empty($waypoints)) {
            $url .= "&waypoints=|"  . urlencode(implode('|', $waypoints)) . "|";
        }

        $result = json_decode($this->sendCurl($url), true);

        if ($result) {
            $result['request'] = $requests;
        }

        return $result;
    }
    
    public function staticMap($lat, $lng, $width = 400, $height = 400, $zoom = 18, $type = "satellite")
    {
        $this->setup();

        $url = $this->endPoint . "/staticmap?";

        $url .= "key="  . $this->apiKey;
        $url .= "&center="  . $lat . "," . $lng;
        $url .= "&size="  . $width . "x" . $height;
        $url .= "&zoom="  . $zoom;
        $url .= "&maptype="  . $type;

        return file_get_contents($url);
    }
}