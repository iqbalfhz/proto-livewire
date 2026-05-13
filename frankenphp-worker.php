<?php

declare(strict_types=1);

/**
 * FrankenPHP Worker Bootstrap for Laravel
 *
 * This script boots the Laravel application once and handles all incoming
 * requests in a persistent loop — no cold-start overhead per request.
 */

ignore_user_abort(true);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle requests in a persistent loop
while (frankenphp_handle_request(function () use ($app, $kernel): void {
    $request = Illuminate\Http\Request::capture();

    // Trust Coolify's reverse proxy (sends X-Forwarded-* headers)
    $request->setTrustedProxies(
        ['*'],
        Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
        Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
        Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
        Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
        Illuminate\Http\Request::HEADER_X_FORWARDED_PREFIX,
    );

    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
})) {
    // Clean up memory between requests
    gc_collect_cycles();
}
