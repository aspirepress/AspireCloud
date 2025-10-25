<?php

namespace App\Http\Middleware;

use App\Services\Metrics\MetricsService;
use App\Utils\Regex;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetricsMiddleware
{
    private const string REQUESTS_COUNT = 'metrics_request_count';
    private const string REQUEST_COUNT_ROUTE = 'metrics_request_count_route_';

    public function __construct(
        private MetricsService $metricsService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // skip metrics endpoint itself
        if ($request->is('metrics')) {
            return $response;
        }
        // total requests
        $this->metricsService->increment(self::REQUESTS_COUNT);
        // per route totals
        $route = null;
        $requestRoute = $request->route();

        if ($requestRoute) {
            $name = $requestRoute->getName();
            if ($name) {
                $route = $name;
            }
        }

        if (!$route) {
            $route = $request->path();
        }

        $key = self::REQUEST_COUNT_ROUTE . Regex::replace('/[\/\.\-\{\}\:]/', '_', $route);
        $this->metricsService->increment($key);

        return $response;
    }
}
