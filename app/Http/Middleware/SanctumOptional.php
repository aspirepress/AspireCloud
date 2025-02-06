<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Guard as SanctumGuard;

class SanctumOptional
{
    public function handle(Request $request, Closure $next)
    {
        // It'd be nice to take a param, i.e. 'auth.optional:sanctum', but we're hardwiring Sanctum for now and for good.
        // If we ever drop Sanctum for say, JWT tokens, we'll have to replace all the auth middleware anyway.
        if ($request->bearerToken()) {
            auth('sanctum')->setUser(app(SanctumGuard::class)->user($request));
        }
        return $next($request);
    }
}
