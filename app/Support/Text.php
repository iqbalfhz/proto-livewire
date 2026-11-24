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

    /** Turn plain text into <p> paragraphs, keeping single newlines as <br>. */
    public static function paragraphs(?string $text): string
    {
        return Collection::make(preg_split('/\R\s*\R/u', trim((string) $text)))
            ->map(fn (string $paragraph) => trim($paragraph))
            ->filter()
            ->map(fn (string $paragraph) => '<p>'.nl2br(e($paragraph)).'</p>')
            ->implode('');
    }
}
