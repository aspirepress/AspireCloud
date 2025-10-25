<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class RequireJson
{
    public function handle(Request $request, Closure $next): Response
    {
        // $request->isJson doesn't accept application/nljson as used by /admin/api/v1/import
        // we'll accept anything with 'json' in it, which should be safe from CSRF in any case
        if ($request->isMethodSafe() || Str::contains($request->headers->get('CONTENT_TYPE') ?? '', 'json')) {
            return $next($request);
        }
        throw new UnsupportedMediaTypeHttpException("Content-Type does not contain 'json'");
    }
}
