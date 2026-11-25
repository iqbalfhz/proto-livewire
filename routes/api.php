<?php

use App\Http\Controllers\Api\BlogDraftController;
use App\Http\Middleware\EnsureBlogApiToken;
use Illuminate\Support\Facades\Route;

// Machine access for the n8n blog workflow: it can read titles and add drafts, never publish.
// Throttled before the token check, so failed guesses count against the limit too.
Route::middleware(['throttle:10,1', EnsureBlogApiToken::class])->group(function () {
    Route::get('/posts/titles', [BlogDraftController::class, 'titles'])->name('api.posts.titles');
    Route::post('/posts', [BlogDraftController::class, 'store'])->name('api.posts.store');
});
