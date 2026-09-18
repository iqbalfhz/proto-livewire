<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Render the XML sitemap for the public site.
     */
    public function __invoke(): Response
    {
        $staticRoutes = [
            'landing.home' => '1.0',
            'landing.projects' => '0.8',
            'landing.blog' => '0.8',
            'landing.about' => '0.6',
            'landing.skills' => '0.6',
            'landing.contact' => '0.5',
        ];

        $urls = [];

        foreach ($staticRoutes as $name => $priority) {
            $urls[] = [
                'loc' => route($name),
                'lastmod' => null,
                'priority' => $priority,
            ];
        }

        Post::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->get()
            ->each(function (Post $post) use (&$urls) {
                $urls[] = [
                    'loc' => route('landing.blog.show', $post->slug),
                    'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString(),
                    'priority' => '0.7',
                ];
            });

        Project::ordered()
            ->select(['slug', 'updated_at', 'is_featured'])
            ->get()
            ->each(function (Project $project) use (&$urls) {
                $urls[] = [
                    'loc' => route('landing.projects.show', $project->slug),
                    'lastmod' => $project->updated_at?->toAtomString(),
                    'priority' => $project->is_featured ? '0.8' : '0.6',
                ];
            });

        return response()
            ->view('feeds.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
