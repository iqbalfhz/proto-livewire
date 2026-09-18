<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Housekeeping for images uploaded through the admin panel.
 *
 * Every image lives on the "public" disk; nothing else references these files,
 * so once the record pointing at one is gone the file is dead weight.
 */
class UploadedImages
{
    /**
     * Delete the given paths from the public disk, ignoring blanks and misses.
     */
    public static function delete(?string ...$paths): void
    {
        $disk = Storage::disk('public');

        foreach (array_filter($paths) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    /**
     * Delete the previous file once it has been superseded by a new upload.
     */
    public static function replace(?string $previous, ?string $current): void
    {
        if ($previous && $previous !== $current) {
            static::delete($previous);
        }
    }

    /**
     * Storage paths of the images embedded in a rich text body by the editor.
     *
     * @return array<int, string>
     */
    public static function embeddedIn(?string $html): array
    {
        preg_match_all('#/storage/(posts/content/[\w./-]+)#i', (string) $html, $matches);

        return array_values(array_unique($matches[1]));
    }
}
