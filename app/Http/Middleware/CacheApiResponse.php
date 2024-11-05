<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_decode;

class CacheApiResponse
{
    /**
     * Default cache duration in minutes
     */
    protected int $cacheDuration = 60;

    /**
     * Routes that should not be cached
     *
     *  @var array<int, string>
     */
    protected array $excludedRoutes = [
        'secret-key/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request);
        $duration = $this->getCacheDuration($request);

        if (Cache::has($cacheKey)) {
            $cachedContent = Cache::get($cacheKey);
            // Decode the cached JSON string
            $decodedContent = json_decode($cachedContent);

            return response()->json(
                $decodedContent,
                200,
                [
                    'X-AspireCloud-Cache' => 'HIT',
                    'X-AspireCloud-Cache-Key' => $cacheKey,
                    'X-AspireCloud-Cache-TTL' => $duration,
                ]
            );
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            Cache::put(
                $cacheKey,
                $response->getContent(),
                now()->addMinutes($duration)
            );
        }

        return $response->withHeaders([
            'X-AspireCloud-Cache' => 'MISS',
            'X-AspireCloud-Cache-Key' => $cacheKey,
            'X-AspireCloud-Cache-TTL' => $duration,
        ]);
    }

    protected function isExcludedRoute(Request $request): bool
    {
        $currentPath = $request->path();

        foreach ($this->excludedRoutes as $route) {
            if (Str::is($route, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    public function getCacheDuration(Request $request): int
    {
        $routeSpecificDurations = [
            // We can add specific routes with custom cache durations here if needed
        ];

        $path = $request->path();
        /** @phpstan-ignore-next-line */
        foreach ($routeSpecificDurations as $route => $duration) {
            if (Str::is($route, $path)) {
                return $duration;
            }
        }

        return $this->cacheDuration;
    }

    public function generateCacheKey(Request $request): string
    {
        $components = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'query' => $request->query(),
        ];

        if (in_array($request->method(), ['POST', 'PUT']) && $request->getContent()) {
            $components['body'] = $request->getContent();
        }

        // Sort the query parameters to ensure the cache key is consistent
        if (!empty($components['query'])) {
            ksort($components['query']);
        }

        return 'aspirecloud_api_' . hash('sha256', serialize($components));
    }
}
