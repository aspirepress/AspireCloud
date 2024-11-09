<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CacheApiResponse
{
    /**
     * Routes that should not be cached
     *
     * @var array<int, string>
     */
    protected array $excludedRoutes = [
        'secret-key/*',
        'download/*',
    ];

    /**
     * Default cache control settings by HTTP method
     * TODO: Move to config file??
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $defaultSettings = [
        'GET' => [
            'max-age' => 3600,                  // 1 hour
            's-maxage' => 21600,                // 6 hours for CDN
            'stale-while-revalidate' => 86400,  // 24 hours
            'public' => true,
        ],
        'POST' => [
            'max-age' => 300,                   // 5 minutes
            's-maxage' => 3600,                 // 1 hour for CDN
            'stale-while-revalidate' => 7200,   // 2 hours
            'public' => true,
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // Remove default Laravel cache headers
        $response->headers->remove('Cache-Control');
        $response->headers->remove('Pragma');

        // Skip if route is excluded
        if ($this->isExcludedRoute($request)) {
            return $response;
        }

        // Get cache settings based on HTTP method
        $settings = $this->defaultSettings[$request->method()] ?? $this->defaultSettings['GET'];

        // Generate ETag based on response content
        $etag = $this->generateETag($request, $response);

        // Check if client sent If-None-Match header
        $requestEtag = $request->header('If-None-Match');

        // If ETag matches, return 304 Not Modified
        if ($requestEtag === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        // Build Cache-Control header
        $cacheControl = $this->buildCacheControlHeader($settings);

        // Set cache headers
        return $response->withHeaders([
            'ETag' => $etag,
            'Cache-Control' => $cacheControl,
            'Vary' => 'Accept-Encoding',
        ]);
    }

    /**
     * Generate ETag for the response
     */
    protected function generateETag(Request $request, Response $response): string
    {
        // Include relevant request data in ETag generation
        $etagData = [
            'path' => $request->path(),
            // Sort the query parameters to ensure consistent ETag
            'query' => collect($request->query())->sortKeys()->all(),
        ];

        // For POST requests, include the request body in the ETag
        if ($request->isMethod('POST')) {
            $etagData['body'] = $request->all();
        }

        // Include response content
        $etagData['content'] = $response->getContent();

        // Generate hash
        $hash = hash('xxh3', serialize($etagData));

        return '"' . $hash . '"';
    }

    /**
     * Build Cache-Control header from settings
     *
     * @param  array<string, mixed>  $settings
     */
    protected function buildCacheControlHeader(array $settings): string
    {
        $parts = [];

        // Add public/private
        $parts[] = $settings['public'] ? 'public' : 'private';

        // Add max-age
        if (isset($settings['max-age'])) {
            $parts[] = 'max-age=' . $settings['max-age'];
        }

        // Add s-maxage for CDN if present
        if (isset($settings['s-maxage'])) {
            $parts[] = 's-maxage=' . $settings['s-maxage'];
        }

        // Add stale-while-revalidate if present
        if (isset($settings['stale-while-revalidate'])) {
            $parts[] = 'stale-while-revalidate=' . $settings['stale-while-revalidate'];
        }

        return implode(', ', $parts);
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
}
