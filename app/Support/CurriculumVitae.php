<?php

namespace App\Support;

use App\Enums\ExperienceType;
use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteContent;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class CurriculumVitae
{
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
