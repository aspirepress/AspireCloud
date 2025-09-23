<?php

use App\Models\Package;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    Package::truncate();
});

function package_information_uri(string $did): string
{
    return '/packages/' . $did;
}

it('returns 404 when did is missing', function () {
    // Catch abort(404) from controller and convert to testable response
    $this->withoutExceptionHandling();
    $this->expectException(NotFoundHttpException::class);

    $this->getJson('/packages/');
});

it('returns 404 when package does not exist', function () {
    Package::factory(3)->create();

    $this->withoutExceptionHandling();
    $this->expectException(NotFoundHttpException::class);

    $this->getJson(package_information_uri('fake:non-existent-package'));
});

it('returns package information in FAIR format', function () {
    Package::factory()
        ->withAuthors()
        ->withReleases(3)
        ->withMetas()
        ->create([
            'did' => 'fake:test-package',
            'name' => 'Test Package',
            'slug' => 'test-package',
            'origin' => 'wp',
            'type' => 'wp-plugin',
            'license' => 'GPLv2',
            'raw_metadata' => [],
        ]);

    $this->getJson(package_information_uri('fake:test-package'))
        ->assertStatus(200)
        ->assertJsonStructure(
            [
                '@context',
                'id',
                'type',
                'license',
                'authors',
                'security',
                'releases',
                'slug',
                'name',
                'description',
            ],
        );
});

it('returns package information in FAIR format with optional fields', function () {
    Package::factory()
        ->withAuthors()
        ->withReleases(3)
        ->withTags()
        ->withMetas([
            'sections' => [
                'installation' => 'Installation instructions here.',
                'changelog' => 'Changelog details here.',
            ],
        ])
        ->create([
            'did' => 'fake:test-package2',
            'name' => 'Test Package2',
            'slug' => 'test-package2',
            'origin' => 'wp',
            'type' => 'wp-theme',
            'license' => 'MIT',
            'raw_metadata' => [],
        ]);

    $this->getJson(package_information_uri('fake:test-package2'))
        ->assertStatus(200)
        ->assertJsonStructure(
            [
                '@context',
                'id',
                'type',
                'license',
                'authors',
                'security',
                'releases',
                'keywords',
                'sections',
                'slug',
                'name',
                'description',
            ],
        );
});
