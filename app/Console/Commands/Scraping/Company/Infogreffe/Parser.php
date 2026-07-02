<?php

namespace App\Console\Commands\Scraping\Company\Infogreffe;

use App\Utils\Scraping\Parser as ScrapingParser;

class Parser extends ScrapingParser
{
    public function parse($dom) {
        $result = [];
        $classname = "deno";
        $elements = $dom->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]"); // Example of finding all <h1> tags

        // Loop through found elements
        foreach ($elements as $element) {
            $result[] = $element->nodeValue;  // Print content of the <h1> tags
        }

        return $result;
    }
}
