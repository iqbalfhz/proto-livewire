<?php

namespace App\Support;

use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use League\CommonMark\CommonMarkConverter;

/**
 * Turns Markdown into the markup the Quill editor itself writes, because the editor
 * drops plain <ul> and <pre> blocks when it opens a post.
 */
class MarkdownArticle
{
    public static function toEditorHtml(string $markdown): string
    {
        $converter = new CommonMarkConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $body = HtmlSanitizer::parseBody($converter->convert($markdown)->getContent());

        if (! $body) {
            return '';
        }

        $xpath = new DOMXPath($body->ownerDocument);

        static::dropImages($xpath, $body);
        static::convertCodeBlocks($xpath, $body);
        static::convertLists($xpath, $body);
        static::convertQuotes($xpath, $body);
        static::convertHeadings($xpath, $body);
        static::dropWhitespace($body);

        return HtmlSanitizer::innerHtml($body);
    }

    protected static function dropImages(DOMXPath $xpath, DOMElement $body): void
    {
        foreach (iterator_to_array($xpath->query('.//img', $body)) as $image) {
            $image->parentNode->removeChild($image);
        }

        foreach (iterator_to_array($xpath->query('.//p[not(node()[normalize-space() or self::*])]', $body)) as $empty) {
            $empty->parentNode->removeChild($empty);
        }
    }

    /** One <div class="ql-code-block"> per line, inside Quill's container. */
    protected static function convertCodeBlocks(DOMXPath $xpath, DOMElement $body): void
    {
        $document = $body->ownerDocument;

        foreach (iterator_to_array($xpath->query('.//pre', $body)) as $pre) {
            $container = $document->createElement('div');
            $container->setAttribute('class', 'ql-code-block-container');

            foreach (explode("\n", rtrim($pre->textContent, "\n")) as $line) {
                $row = $document->createElement('div');
                $row->setAttribute('class', 'ql-code-block');
                $row->appendChild($line === '' ? $document->createElement('br') : $document->createTextNode($line));
                $container->appendChild($row);
            }

            $pre->parentNode->replaceChild($container, $pre);
        }
    }

    /** Quill keeps every list as one flat <ol>, with data-list for the kind and ql-indent-N for nesting. */
    protected static function convertLists(DOMXPath $xpath, DOMElement $body): void
    {
        foreach (iterator_to_array($xpath->query('.//*[self::ul or self::ol][not(ancestor::li)]', $body)) as $list) {
            $flat = $body->ownerDocument->createElement('ol');
            static::flattenList($list, 0, $flat);
            $list->parentNode->replaceChild($flat, $list);
        }
    }

    protected static function flattenList(DOMElement $list, int $depth, DOMElement $flat): void
    {
        $document = $flat->ownerDocument;

        foreach (iterator_to_array($list->childNodes) as $item) {
            if (! $item instanceof DOMElement || $item->tagName !== 'li') {
                continue;
            }

            $entry = $document->createElement('li');
            $entry->setAttribute('data-list', $list->tagName === 'ul' ? 'bullet' : 'ordered');

            if ($depth > 0) {
                $entry->setAttribute('class', 'ql-indent-'.min($depth, 8));
            }

            $nested = [];

            foreach (iterator_to_array($item->childNodes) as $child) {
                if ($child instanceof DOMElement && in_array($child->tagName, ['ul', 'ol'], true)) {
                    $nested[] = $child;
                } elseif ($child instanceof DOMElement && $child->tagName === 'p') {
                    static::appendInline($entry, $child);
                } else {
                    $entry->appendChild($child);
                }
            }

            static::trimText($entry);
            $flat->appendChild($entry);

            foreach ($nested as $sublist) {
                static::flattenList($sublist, $depth + 1, $flat);
            }
        }
    }

    /** Quill quotes are one <blockquote> per line, holding inline content only. */
    protected static function convertQuotes(DOMXPath $xpath, DOMElement $body): void
    {
        $document = $body->ownerDocument;

        foreach (iterator_to_array($xpath->query('.//blockquote[not(ancestor::blockquote)]', $body)) as $quote) {
            $lines = './/p | .//li | .//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6 | .//div[@class="ql-code-block"]';

            foreach (iterator_to_array($xpath->query($lines, $quote)) as $line) {
                $replacement = $document->createElement('blockquote');
                static::appendInline($replacement, $line);
                static::trimText($replacement);
                $quote->parentNode->insertBefore($replacement, $quote);
            }

            $quote->parentNode->removeChild($quote);
        }
    }

    /** The article title is the page's only <h1>, and the editor offers levels 1 to 4. */
    protected static function convertHeadings(DOMXPath $xpath, DOMElement $body): void
    {
        foreach (['h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4'] as $from => $to) {
            foreach (iterator_to_array($xpath->query(".//{$from}", $body)) as $heading) {
                $replacement = $body->ownerDocument->createElement($to);
                static::appendInline($replacement, $heading);
                $heading->parentNode->replaceChild($replacement, $heading);
            }
        }
    }

    /** CommonMark puts newlines between blocks; Quill would keep them as stray text. */
    protected static function dropWhitespace(DOMElement $body): void
    {
        foreach (iterator_to_array($body->childNodes) as $node) {
            if ($node instanceof DOMText && trim($node->textContent) === '') {
                $body->removeChild($node);
            }
        }
    }

    protected static function appendInline(DOMElement $target, DOMNode $source): void
    {
        if ($target->hasChildNodes()) {
            $target->appendChild($target->ownerDocument->createTextNode(' '));
        }

        while ($source->firstChild) {
            $target->appendChild($source->firstChild);
        }
    }

    protected static function trimText(DOMElement $element): void
    {
        $element->normalize();

        if ($element->firstChild instanceof DOMText) {
            $element->firstChild->nodeValue = ltrim($element->firstChild->nodeValue);
        }

        if ($element->lastChild instanceof DOMText) {
            $element->lastChild->nodeValue = rtrim($element->lastChild->nodeValue);
        }
    }
}
