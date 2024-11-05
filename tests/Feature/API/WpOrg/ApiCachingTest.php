<?php

use App\Http\Middleware\CacheApiResponse;
use App\Models\WpOrg\Plugin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

// Cache Middleware Tests

it('bypasses cache for excluded routes', function () {
    $middleware = new CacheApiResponse();
    $request    = Request::create('/secret-key/1.0');

    $called = false;
    $next   = function ($request) use (&$called) {
        $called = true;

        return new Response(['data' => 'test'], 200);
    };

    $response = $middleware->handle($request, $next);

    expect($called)->toBeTrue()
        ->and(Cache::get($middleware->generateCacheKey($request)))->toBeNull()
        ->and($response->headers->has('X-AspireCloud-Cache'))->toBeFalse();
});

it('caches successful responses', function () {
    $middleware = new CacheApiResponse();
    $request    = Request::create('/plugins/info/1.2');
    $testData   = ['plugin' => 'test-data'];

    $next = function ($request) use ($testData) {
        return new Response($testData, 200);
    };

    $response1 = $middleware->handle($request, $next);
    $cacheKey  = $middleware->generateCacheKey($request);

    expect(Cache::has($cacheKey))->toBeTrue()
        ->and($response1->headers->get('X-AspireCloud-Cache'))->toBe('MISS')
        ->and(json_decode($response1->getContent(), true))->toBe($testData);
});

it('generates different cache keys for different request methods', function () {
    $middleware = new CacheApiResponse();
    $url        = '/plugins/info/1.2';

    $getRequest  = Request::create($url);
    $postRequest = Request::create($url, 'POST');

    expect($middleware->generateCacheKey($getRequest))
        ->not->toBe($middleware->generateCacheKey($postRequest));
});

it('make a real request and get the cached response', function () {
    // Create test plugins
    Plugin::factory(2)->create();
    expect(Plugin::query()->count())->toBe(2);

    // Make first request
    $response1 = $this->getJson('/plugins/info/1.2?action=query_plugins');
    $response1->assertStatus(200);

    // Get a cache key from response header
    $cacheKey = $response1->headers->get('X-AspireCloud-Cache-Key');
    $responseData = $response1->json();

    // Assert first request behavior
    expect(Cache::has($cacheKey))->toBeTrue()
        ->and($response1->headers->get('X-AspireCloud-Cache'))->toBe('MISS')
        ->and($responseData)->toBeArray()
        ->and($responseData)->toHaveKey('plugins')
        ->and(count($responseData['plugins']))->toBe(2);

    // Make second request
    $response2 = $this->getJson('/plugins/info/1.2?action=query_plugins');
    $response2->assertStatus(200);

    // Assert second request uses cache
    expect($response2->headers->get('X-AspireCloud-Cache'))->toBe('HIT')
        ->and($response2->json())->toBe($responseData);
});
