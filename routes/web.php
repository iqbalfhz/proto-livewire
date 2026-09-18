<?php

use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

// ─── Landing Page ────────────────────────────────────────────────────────────
Route::middleware([])->group(function () {
    Route::livewire('/', 'pages::landing.home')->name('landing.home');
    Route::livewire('/blog', 'pages::landing.blog')->name('landing.blog');
    Route::livewire('/blog/{slug}', 'pages::landing.blog-show')->name('landing.blog.show');
    Route::livewire('/projects', 'pages::landing.projects')->name('landing.projects');
    Route::livewire('/projects/{slug}', 'pages::landing.project-show')->name('landing.projects.show');
    Route::livewire('/about', 'pages::landing.about')->name('landing.about');
    Route::livewire('/skills', 'pages::landing.skills')->name('landing.skills');
    Route::livewire('/contact', 'pages::landing.contact')->name('landing.contact');
});

// ─── Crawler endpoints ───────────────────────────────────────────────────────
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('feed.xml', FeedController::class)->name('feed.rss');

Route::get('robots.txt', fn () => response()
    ->make("User-agent: *\nDisallow: /admin\n\nSitemap: ".route('sitemap')."\n")
    ->header('Content-Type', 'text/plain; charset=UTF-8'))->name('robots');

// ─── Admin Panel ─────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', ValidateSessionWithWorkOS::class, 'admin'])
    ->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('admin.dashboard');
        Route::livewire('/home', 'pages::admin.home-editor')->name('admin.home');
        Route::livewire('/blog', 'pages::admin.blog-index')->name('admin.blog.index');
        Route::livewire('/blog/create', 'pages::admin.blog-form')->name('admin.blog.create');
        Route::livewire('/blog/{post}/edit', 'pages::admin.blog-form')->name('admin.blog.edit');
        Route::livewire('/projects', 'pages::admin.projects-index')->name('admin.projects.index');
        Route::livewire('/projects/create', 'pages::admin.projects-form')->name('admin.projects.create');
        Route::livewire('/projects/{project}/edit', 'pages::admin.projects-form')->name('admin.projects.edit');
        Route::livewire('/skills', 'pages::admin.skills-index')->name('admin.skills.index');
        Route::livewire('/about', 'pages::admin.about-editor')->name('admin.about');
        Route::livewire('/contact', 'pages::admin.contact-editor')->name('admin.contact');
        Route::livewire('/messages', 'pages::admin.messages-index')->name('admin.messages.index');
        Route::post('/upload-image', ImageUploadController::class)
            ->middleware('throttle:image-upload')
            ->name('admin.upload-image');
    });

// The admin panel is the only authenticated area, so "dashboard" points there.
Route::redirect('dashboard', '/admin')->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
