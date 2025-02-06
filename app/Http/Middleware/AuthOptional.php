<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthOptional
{
    public function handle(Request $request, Closure $next, string $gate): Response
    {
        if ($request->bearerToken()) {
            $user = auth($gate)->user() or throw new UnauthorizedHttpException('Invalid authentication token');
            auth($gate)->setUser($user);
        }
        return $next($request);
    }
}
