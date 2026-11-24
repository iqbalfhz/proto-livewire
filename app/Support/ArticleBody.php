<?php

namespace App\Support;

use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

/** Prepares stored article HTML for reading: TOC anchors and real code blocks. */
class ArticleBody
{
    /**
     * @param  array<int, array{id: string, text: string, level: int}>  $headings
     */
    public function __construct(
        public readonly string $html,
        public readonly array $headings,
    ) {}

    public static function from(?string $html): self
    {
        // Re-cleaned on read so rows saved under older sanitiser rules are covered too.
        $body = HtmlSanitizer::parseBody(HtmlSanitizer::clean($html));

        if (! $body) {
            return new self('', []);
        }

        $xpath = new DOMXPath($body->ownerDocument);

        static::normaliseCodeBlocks($body, $xpath);
        $headings = static::anchorHeadings($body, $xpath);

        return new self(HtmlSanitizer::innerHtml($body), $headings);
    }

    protected static function normaliseCodeBlocks(DOMElement $body, DOMXPath $xpath): void
    {
        $document = $body->ownerDocument;
        $blocks = $xpath->query('.//div[contains(@class, "ql-code-block-container")] | .//pre[contains(@class, "ql-syntax")]', $body);

        foreach (iterator_to_array($blocks) as $block) {
            $lines = iterator_to_array($xpath->query('./div[contains(@class, "ql-code-block")]', $block));
            $text = $lines === []
                ? $block->textContent
                : implode("\n", array_map(fn (DOMElement $line) => $line->textContent, $lines));

            $code = $document->createElement('code');
            $code->appendChild($document->createTextNode($text));

            $pre = $document->createElement('pre');
            $pre->appendChild($code);

            $block->parentNode->replaceChild($pre, $block);
        }
    }

    /**
     * @return array<int, array{id: string, text: string, level: int}>
     */
    protected static function anchorHeadings(DOMElement $body, DOMXPath $xpath): array
    {
        $headings = [];
        $used = [];

        foreach ($xpath->query('.//h1 | .//h2 | .//h3', $body) as $heading) {
            $text = trim($heading->textContent);

            if ($text === '') {
                continue;
            }

            // Prefixed so an author's heading can never collide with a page element id.
            $base = 'section-'.(Str::slug($text) ?: 'untitled');
            $id = $base;

            for ($suffix = 2; isset($used[$id]); $suffix++) {
                $id = "{$base}-{$suffix}";
            }

            $used[$id] = true;
            $heading->setAttribute('id', $id);
            $headings[] = ['id' => $id, 'text' => $text, 'level' => (int) substr($heading->nodeName, 1)];
        }

        return $headings;
    }
}
