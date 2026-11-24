<?php

namespace App\Support;

use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

/**
 * Prepares stored article HTML for reading: anchors for the table of contents
 * and real <pre><code> blocks in place of the editor's per-line markup.
 */
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
        $body = HtmlSanitizer::parseBody((string) $html);

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
            $used[$base] = ($used[$base] ?? 0) + 1;
            $id = $used[$base] === 1 ? $base : "{$base}-{$used[$base]}";

            $heading->setAttribute('id', $id);
            $headings[] = ['id' => $id, 'text' => $text, 'level' => (int) substr($heading->nodeName, 1)];
        }

        return $headings;
    }
}
