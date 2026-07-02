<?php

namespace App\Utils\Scraping;

use DOMDocument;
use DOMXPath;

class Scraper
{
    /**
     * 
    */
    public function scrape($url) {
        $content = $this->getContent($url);

        if ($content) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($content);
            libxml_clear_errors();
            return new DOMXPath($dom);
        }

        return null;
    }

    /**
     * 
    */
    protected function getContent($url)
    {
        $ch = curl_init();

        // Set the URL to fetch
        curl_setopt($ch, CURLOPT_URL, $url);
        
        // Return the transfer as a string instead of outputting it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Execute the request
        $html = curl_exec($ch);
        
        // Close cURL session
        curl_close($ch);

        return $html;
    }
}
