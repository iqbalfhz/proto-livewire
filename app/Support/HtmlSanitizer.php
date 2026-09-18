<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list sanitiser for rich text coming out of the editor.
 *
 * The editor is admin-only today, but its output is rendered unescaped, so one
 * compromised or careless author would be enough for stored XSS. Rather than
 * pull in a dependency, this keeps the small, known tag set the editor produces
 * and drops everything else.
 */
class HtmlSanitizer
{
    /** Tags kept as-is. Anything else is unwrapped, keeping its text. */
    protected const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'span', 'div',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code',
        'ol', 'ul', 'li', 'a', 'img',
        'table', 'thead', 'tbody', 'tr', 'td', 'th',
    ];

    /** Tags dropped together with everything inside them. */
    protected const FORBIDDEN_TREES = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed',
        'applet', 'form', 'input', 'textarea', 'select', 'button',
        'link', 'meta', 'base', 'svg', 'math',
    ];

    /** Attributes kept, per tag. The '*' entry applies to every allowed tag. */
    protected const ALLOWED_ATTRIBUTES = [
        '*' => ['class', 'style'],
        'a' => ['href', 'target', 'rel', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'ol' => ['start'],
        'li' => ['data-list'],
        'td' => ['colspan', 'rowspan', 'data-row'],
        'th' => ['colspan', 'rowspan'],
    ];

    /** CSS properties the editor sets through inline styles. */
    protected const ALLOWED_STYLES = [
        'color', 'background-color', 'text-align', 'text-indent', 'width', 'height',
    ];

    /** URL schemes considered safe for href and src. */
    protected const ALLOWED_SCHEMES = ['http', 'https', 'mailto'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument;

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach (iterator_to_array($dom->childNodes) as $node) {
            if ($node->nodeType === XML_PI_NODE) {
                $dom->removeChild($node);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body) {
            return '';
        }

        static::cleanChildren($body);

        $output = '';

        foreach ($body->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    protected static function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement) {
                static::cleanElement($node);

                continue;
            }

            // Comments can hide payloads for sloppy downstream parsers.
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE) {
                $parent->removeChild($node);
            }
        }
    }

    protected static function cleanElement(DOMElement $element): void
    {
        $tag = strtolower($element->nodeName);

        if (in_array($tag, static::FORBIDDEN_TREES, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        if (! in_array($tag, static::ALLOWED_TAGS, true)) {
            static::unwrap($element);

            return;
        }

        static::cleanAttributes($element, $tag);
        static::cleanChildren($element);
    }

    protected static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge(
            static::ALLOWED_ATTRIBUTES['*'],
            static::ALLOWED_ATTRIBUTES[$tag] ?? []
        );

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! static::isSafeUrl($attribute->nodeValue)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if ($name === 'style') {
                $style = static::cleanStyle($attribute->nodeValue);

                if ($style === '') {
                    $element->removeAttribute('style');
                } else {
                    $element->setAttribute('style', $style);
                }
            }
        }

        // Anything opening a new tab needs the opener severed.
        if ($tag === 'a' && $element->getAttribute('target') !== '') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    /**
     * Replace an element with its children, keeping the readable content.
     */
    protected static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        static::cleanChildren($element);

        foreach (iterator_to_array($element->childNodes) as $child) {
            $parent->insertBefore($child, $element);
        }

        $parent->removeChild($element);
    }

    protected static function isSafeUrl(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        // Relative, root-relative and anchor links never carry a scheme.
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        if (! str_contains($url, ':')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, static::ALLOWED_SCHEMES, true);
    }

    protected static function cleanStyle(?string $style): string
    {
        $kept = [];

        foreach (explode(';', (string) $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);

            if (! in_array($property, static::ALLOWED_STYLES, true)) {
                continue;
            }

            // url() and expression() are the usual ways to smuggle script in.
            if (preg_match('/url\s*\(|expression\s*\(|javascript:/i', $value)) {
                continue;
            }

            $kept[] = "{$property}: {$value}";
        }

        return implode('; ', $kept);
    }
}
