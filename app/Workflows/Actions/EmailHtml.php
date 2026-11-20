<?php

namespace App\Workflows\Actions;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * The HTML of an automatic email is written in the browser, source included:
 * what can run in a mail client or in the CRM is removed before it is stored
 * in a message (scripts, frames, forms, event handlers, "javascript:" links).
 * Formatting - the point of the editor - is kept.
 */
class EmailHtml
{
    protected const FORBIDDEN_TAGS = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta', 'base', 'style', 'applet', 'frame', 'frameset'];

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // The wrapper keeps the fragment as it is (no <html><body> around it)
        $document->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        foreach (self::FORBIDDEN_TAGS as $tag) {
            foreach (iterator_to_array($xpath->query("//{$tag}")) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        foreach ($xpath->query('//*') as $element) {
            /** @var DOMElement $element */
            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = strtolower(preg_replace('/[\s\x00-\x1f]+/', '', $attribute->value));

                $unsafeUrl = in_array($name, ['href', 'src', 'xlink:href', 'action', 'formaction'], true)
                    && (str_starts_with($value, 'javascript:') || str_starts_with($value, 'vbscript:') || (str_starts_with($value, 'data:') && !str_starts_with($value, 'data:image/')));
                $unsafeStyle = $name === 'style' && (str_contains($value, 'expression(') || str_contains($value, 'javascript:') || str_contains($value, 'url('));

                if (str_starts_with($name, 'on') || $unsafeUrl || $unsafeStyle) {
                    $element->removeAttribute($attribute->name);
                }
            }
        }

        $root = $document->getElementById('__root');
        $clean = '';

        foreach ($root ? $root->childNodes : [] as $child) {
            $clean .= $document->saveHTML($child);
        }

        return $clean;
    }
}
