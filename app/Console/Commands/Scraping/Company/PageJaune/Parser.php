<?php

namespace App\Console\Commands\Scraping\Company\PageJaune;

use App\Utils\Scraping\Parser as ScrapingParser;

class Parser extends ScrapingParser
{
    public function parse($dom) {
        $result = [];
        $classname = "bi-denomination";
        $elements = $dom->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]"); // Example of finding all <h1> tags

        // Loop through found elements
        foreach ($elements as $element) {
            $result[] = $element->getAttribute('href');  // Print content of the <h1> tags
        }

        return $result;
    }
}
