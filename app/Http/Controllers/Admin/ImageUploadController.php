<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store('posts/content', 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
        ]);
    }
}
