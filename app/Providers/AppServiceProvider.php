<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsAdmin;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureModels();
        $this->configureRateLimiting();
        $this->configureLivewire();
    }

    /**
     * Re-apply the admin guards on every Livewire update request.
     *
     * Livewire only replays a small built-in whitelist of middleware when a
     * component action is called, so without this a revoked admin (or a
     * revoked WorkOS session) could keep writing through an open admin page.
     */
    protected function configureLivewire(): void
    {
        Livewire::addPersistentMiddleware([
            EnsureUserIsAdmin::class,
            ValidateSessionWithWorkOS::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Enforce strict Eloquent model behavior to surface bugs early.
     */
    protected function configureModels(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
    }

    /**
     * Define named rate limiters for sensitive endpoints.
     */
    protected function configureRateLimiting(): void
    {
        // Max 20 image uploads per minute per authenticated user
        RateLimiter::for('image-upload', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->user()?->id ?? $request->ip());
        });

        // Max 5 contact form submissions per 5 minutes per IP
        RateLimiter::for('contact', function (Request $request): Limit {
            return Limit::perMinutes(5, 5)->by($request->ip());
        });
    }
}
