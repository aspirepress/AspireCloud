<?php

use App\Models\WpOrg\Plugin;

describe('HTTP Cache Middleware', function () {
    beforeEach(function () {
        // Create some test plugins
        Plugin::factory(2)->create();
    });

    it('adds cache headers for plugin query endpoint', function () {
        $response = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins');

        expect($response->getStatusCode())->toBe(200)
            ->and($response->headers->has('ETag'))->toBeTrue()
            ->and($response->headers->has('Cache-Control'))->toBeTrue()
            ->and($response->headers->get('Cache-Control'))->toContain('public')
            ->and($response->headers->get('Cache-Control'))->toContain('max-age=3600')
            ->and($response->headers->get('Cache-Control'))->toContain('s-maxage=21600')
            ->and($response->headers->get('Vary'))->toBe('Accept-Encoding');
    });

    it('returns 304 when ETag matches', function () {
        // First request to get the ETag
        $firstResponse = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins');
        $etag = $firstResponse->headers->get('ETag');

        // Second request with the ETag
        $secondResponse = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins', [], [
            'If-None-Match' => $etag,
        ]);

        expect($secondResponse->getStatusCode())->toBe(304)
            ->and($secondResponse->headers->get('ETag'))->toBe($etag);
    });

    it('generates different ETags for different query parameters', function () {
        $response1 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&page=1');
        $response2 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&page=2');

        expect($response1->headers->get('ETag'))
            ->not->toBe($response2->headers->get('ETag'));
    });

    it('uses different cache settings for POST requests', function () {
        $response = makeApiRequest('POST', '/plugins/update-check/1.1', [
            'plugins' => [
                'test-plugin/test.php' => [
                    'Version' => '1.0',
                ],
            ],
        ]);

        expect($response->headers->get('Cache-Control'))->toContain('max-age=300')
            ->and($response->headers->get('Cache-Control'))->toContain('s-maxage=3600');
    });

    it('excludes specified routes from caching', function () {
        $response = makeApiRequest('GET', '/secret-key/1.0');

        expect($response->headers->has('ETag'))->toBeFalse()
            ->and($response->headers->has('Cache-Control'))->toBeFalse();
    });

    it('maintains same ETag for identical requests', function () {
        $response1 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins');
        $response2 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins');

        expect($response1->headers->get('ETag'))
            ->toBe($response2->headers->get('ETag'));
    });

    it('generates same ETag regardless of parameter order', function () {
        $response1 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&page=1');
        $response2 = makeApiRequest('GET', '/plugins/info/1.2?page=1&action=query_plugins');

        expect($response1->headers->get('ETag'))
            ->toBe($response2->headers->get('ETag'));
    });
});

// Test real-world API endpoints
describe('API Endpoints Cache Behavior', function () {
    beforeEach(function () {
        Plugin::factory(3)->create();
    });

    it('caches plugin info endpoint correctly', function () {
        Plugin::factory()->create(['slug' => 'test-plugin']);
        // First request
        $response1 = makeApiRequest('GET', '/plugins/info/1.2?action=plugin_information&slug=test-plugin');
        $etag1 = $response1->headers->get('ETag');

        expect($response1->getStatusCode())->toBe(200)
            ->and($etag1)->not->toBeNull()
            ->and($response1->headers->get('Cache-Control'))->toContain('public')
            ->and($response1->headers->get('Cache-Control'))->toContain('max-age=3600');

        // Second request with ETag
        $response2 = makeApiRequest('GET', '/plugins/info/1.2?action=plugin_information&slug=test-plugin', [], [
            'If-None-Match' => $etag1,
        ]);

        expect($response2->getStatusCode())->toBe(304);
    });

    it('caches plugin query endpoint correctly', function () {
        // First request
        $response1 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&page=1');

        expect($response1->getStatusCode())->toBe(200)
            ->and($response1->json())->toHaveKey('plugins')
            ->and(count($response1->json()['plugins']))->toBe(3);

        $etag1 = $response1->headers->get('ETag');

        // Second request with ETag
        $response2 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins&page=1', [], [
            'If-None-Match' => $etag1,
        ]);

        expect($response2->getStatusCode())->toBe(304);
    });

    it('generates new ETag when plugin data changes', function () {
        // Initial request
        $response1 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins');
        $etag1 = $response1->headers->get('ETag');

        // Create a new plugin
        Plugin::factory()->create();

        // Second request
        $response2 = makeApiRequest('GET', '/plugins/info/1.2?action=query_plugins');
        $etag2 = $response2->headers->get('ETag');

        expect($etag1)->not->toBe($etag2)
            ->and(count($response2->json()['plugins']))->toBe(4);
    });
});
