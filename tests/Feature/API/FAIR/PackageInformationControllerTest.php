<?php
declare(strict_types=1);

use App\Models\Package;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use App\Values\Packages\PackageData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
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

it('returns package information in FAIR format for a plugin based package', function () {
    $plugin = Plugin::factory()->create([
        'name' => 'Test Plugin',
        'slug' => 'test-plugin',
    ]);

    $package = Package::fromPackageData(PackageData::from($plugin));
    $did = $package->did;

    $this->getJson(package_did_document_uri('wp-plugin', $plugin->slug))
        ->assertStatus(200)
        ->assertJsonStructure(
            [
                '@context',
                'id',
                'alsoKnownAs',
                'verificationMethod',
                'service',
            ],
        )
        ->assertJsonPath('id', $did);
});

it('returns package information in FAIR format for a theme based package', function () {
    $theme = Theme::factory()->create([
        'name' => 'Test Theme',
        'slug' => 'test-theme',
    ]);

    $package = Package::fromPackageData(PackageData::from($theme));
    $did = $package->did;

    $this->getJson(package_did_document_uri('wp-theme', $theme->slug))
        ->assertStatus(200)
        ->assertJsonStructure(
            [
                '@context',
                'id',
                'alsoKnownAs',
                'verificationMethod',
                'service',
            ],
        )
        ->assertJsonPath('id', $did);
});
