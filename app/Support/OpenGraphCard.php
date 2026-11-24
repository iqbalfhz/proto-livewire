<?php

namespace App\Support;

use GdImage;

/**
 * Draws the 1200×630 typographic card shown when a page is shared.
 */
class OpenGraphCard
{
    protected const WIDTH = 1200;

    protected const HEIGHT = 630;

    protected const MARGIN = 72;

    /** Title sizes tried in order until the text fits. */
    protected const TITLE_SIZES = [72, 64, 56, 48, 42, 36];

    public static function render(string $kicker, string $title, string $footer): string
    {
        $serif = resource_path('fonts/InstrumentSerif-Regular.ttf');
        $mono = resource_path('fonts/JetBrainsMono-Variable.ttf');

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $paper = imagecolorallocate($image, 0xF4, 0xF0, 0xE6);
        $ink = imagecolorallocate($image, 0x19, 0x16, 0x12);
        $muted = imagecolorallocate($image, 0x6E, 0x65, 0x59);
        $rule = imagecolorallocate($image, 0xCF, 0xC9, 0xBC);
        $accent = imagecolorallocate($image, 0x7B, 0x2D, 0x26);
        $right = self::WIDTH - self::MARGIN;

        imagefilledrectangle($image, 0, 0, self::WIDTH, self::HEIGHT, $paper);
        imagefilledrectangle($image, 0, 0, self::WIDTH, 7, $accent);

        imagettftext($image, 15, 0, self::MARGIN, 104, $muted, $mono, static::fit(mb_strtoupper($kicker), $mono, 15, $right - self::MARGIN));
        imageline($image, self::MARGIN, 136, $right, 136, $rule);

        static::drawTitle($image, $title, $serif, $ink);

        imageline($image, self::MARGIN, 524, $right, 524, $rule);
        imagefilledrectangle($image, self::MARGIN, 560, self::MARGIN + 9, 569, $accent);
        imagettftext($image, 15, 0, self::MARGIN + 26, 571, $muted, $mono, static::fit(mb_strtoupper($footer), $mono, 15, $right - self::MARGIN - 26));

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    protected static function drawTitle(GdImage $image, string $title, string $font, int $color): void
    {
        $maxWidth = self::WIDTH - self::MARGIN * 2;
        $available = 340;
        $size = self::TITLE_SIZES[0];
        $lineHeight = 0;
        $maxLines = 1;
        $lines = [];

        foreach (self::TITLE_SIZES as $size) {
            $lineHeight = (int) round($size * 1.28);
            $maxLines = max(1, (int) floor($available / $lineHeight));
            $lines = static::wrap($title, $font, $size, $maxWidth);

            if (count($lines) <= $maxLines) {
                break;
            }
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = static::fit($lines[$maxLines - 1].' …', $font, $size, $maxWidth);
        }

        $y = 168 + $size;

        foreach ($lines as $line) {
            imagettftext($image, $size, 0, self::MARGIN, $y, $color, $font, $line);
            $y += $lineHeight;
        }
    }

    /**
     * @return array<int, string>
     */
    protected static function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $lines = [];
        $current = '';

        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            while (static::width($word, $font, $size) > $maxWidth) {
                $length = mb_strlen($word);

                while ($length > 1 && static::width(mb_substr($word, 0, $length), $font, $size) > $maxWidth) {
                    $length--;
                }

                if ($current !== '') {
                    $lines[] = $current;
                    $current = '';
                }

                $lines[] = mb_substr($word, 0, $length);
                $word = mb_substr($word, $length);
            }

            $candidate = $current === '' ? $word : "{$current} {$word}";

            if (static::width($candidate, $font, $size) > $maxWidth) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    protected static function fit(string $text, string $font, int $size, int $maxWidth): string
    {
        if (static::width($text, $font, $size) <= $maxWidth) {
            return $text;
        }

        while ($text !== '' && static::width($text.'…', $font, $size) > $maxWidth) {
            $text = mb_substr($text, 0, -1);
        }

        return rtrim($text).'…';
    }

    protected static function width(string $text, string $font, int $size): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return abs($box[2] - $box[0]);
    }
}
