<?php

namespace App\Console\Commands\Scraping\Company\PageJaune;

use App\Utils\Scraping\Parser as ScrapingParser;

class ProsParser extends ScrapingParser
{
    public function parse($dom) {
        $result = [];

        // Name
        $name = $this->findByTag($dom, "h1");
        if (count($name) > 0) {
            $result['first_name'] = $name[0]->nodeValue;
        }

        // Number
        $numbers = $this->findByClassNamee($dom, "coord-numero");
        if (count($numbers) > 0) {
            $result['phone_number'] = $numbers[0]->nodeValue;
        }

        // Address
        $addresses = $dom->query("//div[contains(@class, 'address-container')]//*[contains(@class, 'noTrad')]");
        if (count($addresses) > 0) {
            $result['street'] = $addresses[0]->nodeValue;
        }

        // Wabsite
        $websites = $dom->query("//div[contains(@class, 'lvs-container')]//*[contains(@class, 'value')]");
        if (count($websites) > 0) {
            $result['website'] = $websites[0]->nodeValue;
        }

        return $result;
    }
}
