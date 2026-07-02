<?php

namespace App\Utils\Scraping;

abstract class Parser
{
    /**
     * 
    */
    abstract public function parse($dom);

    /**
     * 
    */
    public function findByTag($dom, $tag) {
        return $dom->query("//$tag");
    }

    /**
     * 
    */
    public function findByClassNamee($dom, $className) {
        return $dom->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
    }
}
