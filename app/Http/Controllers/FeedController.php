<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SiteContent;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * Render an RSS 2.0 feed of the most recent published posts.
     */
    public function __invoke(): Response
    {
        $posts = Post::published()->limit(20)->get();

        return response()
            ->view('feeds.rss', [
                'posts' => $posts,
                'siteName' => config('app.name'),
                'siteDescription' => SiteContent::get('about', 'bio', 'Latest articles.'),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
