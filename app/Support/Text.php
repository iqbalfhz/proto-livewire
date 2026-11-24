<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Text
{
    /**
     * Two-digit position used by the numbered index rows, e.g. "03".
     */
    public static function index(int $number): string
    {
        return str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Split a comma-separated field into clean, unique values.
     *
     * @return array<int, string>
     */
    public static function commaList(?string $text): array
    {
        return Collection::make(explode(',', (string) $text))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Turn a plain-text field into real paragraph markup.
     *
     * `nl2br()` only ever produces <br>, which leaves the text as one long
     * block — no paragraph spacing to style and nothing for the drop cap to
     * latch onto. Blank lines become <p>, single newlines stay <br>.
     */
    public static function paragraphs(?string $text): string
    {
        return Collection::make(preg_split('/\R\s*\R/u', trim((string) $text)))
            ->map(fn (string $paragraph) => trim($paragraph))
            ->filter()
            ->map(fn (string $paragraph) => '<p>'.nl2br(e($paragraph)).'</p>')
            ->implode('');
    }
}
