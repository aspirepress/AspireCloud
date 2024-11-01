<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeWpOrgRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (is_array($rq = $request->query('request'))) {
            // flatten 'request' query args into the top-level of the query
            $request->merge($rq);
        }
        return $next($request);
    }
}
