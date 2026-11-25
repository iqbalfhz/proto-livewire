<?php

namespace App\Support;

use App\Enums\ExperienceType;
use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteContent;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CurriculumVitae
{
    /** Photo size in the PDF, in pixels (4:5). */
    protected const PHOTO_WIDTH = 360;

    protected const PHOTO_HEIGHT = 450;

    /**
     * Everything the CV shows, shared by the web page and its PDF.
     *
     * @return array{
     *     about: array<string, string>,
     *     contact: array<string, string>,
     *     sections: array<string, EloquentCollection<int, Experience>>,
     *     skillGroups: Collection<string, EloquentCollection<int, Skill>>,
     *     projects: EloquentCollection<int, Project>
     * }
     */
    public static function data(): array
    {
        $experiences = Experience::chronological()->get();

        return [
            'about' => SiteContent::group('about'),
            'contact' => SiteContent::group('contact'),
            'sections' => [
                'Experience' => $experiences->where('type', ExperienceType::Work),
                'Education' => $experiences->where('type', ExperienceType::Education),
            ],
            'skillGroups' => Skill::ordered()->get(['name', 'category'])->groupBy('category'),
            'projects' => Project::ordered()->featured()->limit(4)->get(['title', 'slug', 'role', 'year']),
        ];
    }

    /**
     * The profile photo, centre-cropped to 4:5 and shrunk so the PDF stays small, as a JPEG data URI.
     */
    public static function photo(?string $path): ?string
    {
        $disk = Storage::disk('public');

        if (! $path || ! $disk->exists($path)) {
            return null;
        }

        $source = imagecreatefromstring($disk->get($path));
        [$width, $height] = [imagesx($source), imagesy($source)];
        $cropWidth = min($width, intdiv($height * 4, 5));
        $cropHeight = intdiv($cropWidth * 5, 4);

        $photo = imagecreatetruecolor(self::PHOTO_WIDTH, self::PHOTO_HEIGHT);
        imagefill($photo, 0, 0, imagecolorallocate($photo, 255, 255, 255));
        imagecopyresampled(
            $photo, $source, 0, 0, intdiv($width - $cropWidth, 2), intdiv($height - $cropHeight, 2),
            self::PHOTO_WIDTH, self::PHOTO_HEIGHT, $cropWidth, $cropHeight,
        );

        ob_start();
        imagejpeg($photo, null, 85);

        return 'data:image/jpeg;base64,'.base64_encode(ob_get_clean());
    }

    /**
     * Profile links shown as bare addresses, e.g. "github.com/name".
     *
     * @param  array<string, string>  $about
     * @return array<string, string> Display text keyed by URL.
     */
    public static function links(array $about): array
    {
        return collect(['github_url', 'linkedin_url'])
            ->map(fn (string $key) => $about[$key] ?? null)
            ->filter()
            ->mapWithKeys(fn (string $url) => [$url => preg_replace('#^https?://(www\.)?#', '', rtrim($url, '/'))])
            ->all();
    }
}
