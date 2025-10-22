<?php

namespace Tests\Feature\API\Metrics;

use App\Models\Metric;
use App\Models\Package;

beforeEach(function () {
    Metric::truncate();
    Package::truncate();
});

function package_information_uri(string $did): string
{
    return '/packages/' . $did;
}

function package_did_document_uri(string $packageType, string $slug): string
{
    return '/packages/' . $packageType . '/' . $slug . '/did.json';
}

test('metrics endpoint returns correct data', function () {
    Package::factory()
        ->withAuthors()
        ->withReleases(1)
        ->withMetas()
        ->create([
            'did' => 'fake:test-plugin-package',
            'name' => 'Test Plugin Package',
            'slug' => 'test-plugin-package',
            'origin' => 'wp',
            'type' => 'wp-plugin',
            'license' => 'GPLv2',
            'raw_metadata' => [],
        ]);

    Package::factory()
        ->withAuthors()
        ->withReleases(1)
        ->withMetas()
        ->create([
            'did' => 'fake:test-theme-package',
            'name' => 'Test Theme Package',
            'slug' => 'test-theme-package',
            'origin' => 'wp',
            'type' => 'wp-theme',
            'license' => 'GPLv2',
            'raw_metadata' => [],
        ]);

    $this->get('/metrics')
        ->assertStatus(200)
        ->assertSeeText('requests_total 0');

    // Request the DID document endpoint.
    $this->getJson(package_did_document_uri('wp-plugin', 'test-plugin-package'))
        ->assertStatus(200);

    $response = $this->get('/metrics');
    $response->assertStatus(200);
    $decoded = html_entity_decode($response->getContent(), ENT_QUOTES);

    $this->assertStringContainsString('requests_total 1', $decoded);
    $this->assertStringContainsString('requests_by_route_total{route="package.didDocument"} 1', $decoded);

    // Request the package metadata endpoint, 2 times.
    $this->getJson(package_information_uri('fake:test-plugin-package'))
        ->assertStatus(200);
    $this->getJson(package_information_uri('fake:test-theme-package'))
        ->assertStatus(200);

    $response = $this->get('/metrics');

    $response->assertStatus(200);
    $decoded = html_entity_decode($response->getContent(), ENT_QUOTES);
    $this->assertStringContainsString('requests_total 3', $decoded);
    $this->assertStringContainsString('requests_by_route_total{route="package.didDocument"} 1', $decoded);
    $this->assertStringContainsString('requests_by_route_total{route="package.fairMetadata"} 2', $decoded);
});
