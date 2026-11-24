<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteContent;
use App\Support\OpenGraphCard;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class OpenGraphImageController extends Controller
{
    /**
     * Serve the share card for a published article or a project.
     */
    public function __invoke(string $type, string $slug): Response
    {
        [$record, $kicker] = $type === 'posts' ? $this->post($slug) : $this->project($slug);

        $footer = implode(' · ', array_filter([
            SiteContent::get('about', 'name', config('app.name')),
            SiteContent::get('about', 'role'),
        ]));

        $disk = Storage::disk('local');
        $prefix = "{$type}-{$record->getKey()}-";
        $path = 'og/'.$prefix.md5(implode('|', [$kicker, $record->title, $footer])).'.png';

        if (! $disk->exists($path)) {
            $disk->delete(array_filter($disk->files('og'), fn (string $file) => str_starts_with(basename($file), $prefix)));
            $disk->put($path, OpenGraphCard::render($kicker, $record->title, $footer));
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return array{0: Post, 1: string}
     */
    protected function post(string $slug): array
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return [$post, 'Article'.($post->published_at ? ' · '.$post->published_at->format('d M Y') : '')];
    }

    /**
     * @return array{0: Project, 1: string}
     */
    protected function project(string $slug): array
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        return [$project, 'Project'.($project->year ? ' · '.$project->year : '')];
    }
}
