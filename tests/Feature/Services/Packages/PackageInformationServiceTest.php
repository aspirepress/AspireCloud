<?php

declare(strict_types=1);

use App\Models\Package;
use App\Services\Packages\PackageInformationService;

beforeEach(function () {
    Package::truncate();
});

test('packageInformation with DID returns matching package', function () {
    // Create packages with specific names
    Package::factory()->create(['did' => 'fake:test-package', 'name' => 'Test Package', 'slug' => 'test-package']);
    Package::factory()->create([
        'did' => 'fake:another-package',
        'name' => 'Another Package',
        'slug' => 'another-package',
    ]);

    // Create the service
    $service = new PackageInformationService();

    $package = $service->findByDID('fake:test-package');
    assert($package !== null);

    // Assert the response contains the matching plugin
    expect($package)
        ->toBeInstanceOf(Package::class)
        ->and($package->name)
        ->toBe('Test Package');
});
