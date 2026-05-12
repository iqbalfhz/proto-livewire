<?php

use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

// ─── Landing Page ────────────────────────────────────────────────────────────
Route::middleware([])->group(function () {
    Route::livewire('/', 'pages::landing.home')->name('landing.home');
    Route::livewire('/blog', 'pages::landing.blog')->name('landing.blog');
    Route::livewire('/blog/{slug}', 'pages::landing.blog-show')->name('landing.blog.show');
    Route::livewire('/projects', 'pages::landing.projects')->name('landing.projects');
    Route::livewire('/about', 'pages::landing.about')->name('landing.about');
    Route::livewire('/skills', 'pages::landing.skills')->name('landing.skills');
    Route::livewire('/contact', 'pages::landing.contact')->name('landing.contact');
});

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
        Route::livewire('/messages', 'pages::admin.messages-index')->name('admin.messages.index');
        Route::post('/upload-image', ImageUploadController::class)
            ->middleware('throttle:image-upload')
            ->name('admin.upload-image');
    });

// ─── App (authenticated team routes) ─────────────────────────────────────────
Route::prefix('{current_team}')
    ->middleware(['auth', ValidateSessionWithWorkOS::class, EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
