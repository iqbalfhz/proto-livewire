<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\MarkdownArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogDraftController extends Controller
{
    /**
     * Recent titles, drafts included, so the writer can avoid repeating a topic.
     */
    public function titles(): JsonResponse
    {
        return response()->json(Post::latest()->limit(200)->pluck('title'));
    }

    /**
     * Save a Markdown article as a draft. Publishing stays a manual step in the admin.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['string', 'max:30'],
            'markdown' => ['required', 'string', 'max:60000'],
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'tags' => array_values($validated['tags'] ?? []),
            'content' => MarkdownArticle::toEditorHtml($validated['markdown']),
            'is_published' => false,
            'published_at' => null,
        ]);

        return response()->json([
            'id' => $post->id,
            'slug' => $post->slug,
            'edit_url' => route('admin.blog.edit', $post),
        ], 201);
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title), 240, '') ?: 'draft';
        $slug = $base;

        for ($suffix = 2; Post::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
