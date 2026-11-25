<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBlogApiToken
{
    /** Shorter secrets are treated as unset, so a weak token never opens the API. */
    protected const MIN_TOKEN_LENGTH = 32;

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('services.blog_api.token');

        if (strlen($token) < self::MIN_TOKEN_LENGTH || ! hash_equals($token, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
