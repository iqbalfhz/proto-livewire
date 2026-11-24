<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Laravel\WorkOS\WorkOS;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        // The Vite dev server needs its own origins, so CSP only applies to built assets
        if (! Vite::isRunningHot()) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($nonce));
        }

        // Prevent clickjacking — only allow embedding from same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // The legacy filter can itself introduce XSS, so it is switched off
        $response->headers->set('X-XSS-Protection', '0');

        // Control referrer information in cross-origin requests
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser features not needed by this app
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Enforce HTTPS for 1 year (production only — avoids issues in local dev)
        if (app()->isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        // Remove server fingerprint headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    /** 'unsafe-eval' is for Alpine; scripts without the nonce stay blocked. */
    protected function contentSecurityPolicy(string $nonce): string
    {
        $workos = parse_url(WorkOS::baseUrl());
        $workosOrigin = $workos['scheme'].'://'.$workos['host'].(isset($workos['port']) ? ':'.$workos['port'] : '');

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' {$workosOrigin}",
            "frame-ancestors 'self'",
        ]);
    }
}
