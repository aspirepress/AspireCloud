<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class MetricsMiddleware
{
    const string REQUESTS_COUNT = 'metrics_request_count';
    const string REQUEST_COUNT_ROUTE = 'metrics_request_count_route_';

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        // skip metrics endpoint itself
        if ($request->is('metrics')) {
            return $response;
        }
        // total requests
        Cache::increment(self::REQUESTS_COUNT);
        // per route totals
        $route = optional($request->route())?->getName() ?? $request->path();
        $key = self::REQUEST_COUNT_ROUTE . str_replace(['/', '.', '-', '{', '}', ':'], '_', $route);
        Cache::increment($key);

        return $response;
    }
}
