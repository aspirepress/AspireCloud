<?php

use App\Enums\AssetType;
use App\Jobs\DownloadAssetJob;
use App\Models\WpOrg\Asset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
    Queue::fake();
    Http::fake([
        '*' => Http::response('bob', 200, ['Content-Type' => 'application/zip', 'Content-Length' => 3]),
    ]);

});

describe('S3 Asset Storage', function () {
    it('stores downloaded plugin in S3', function () {
        // Arrange

        // Act
        $job = new DownloadAssetJob(
            AssetType::PLUGIN,
            'test-plugin',
            'test-plugin.1.0.0.zip',
            'https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip',
        );

        $job->handle();

        // Assert
        Storage::disk('s3')->assertExists('plugins/test-plugin/test-plugin.1.0.0.zip');
        expect(Storage::disk('s3')->get('plugins/test-plugin/test-plugin.1.0.0.zip'))
            ->toBe('bob');
    });

    it('stores downloaded theme in S3', function () {
        $job = new DownloadAssetJob(
            AssetType::THEME,
            'test-theme',
            'test-theme.1.0.0.zip',
            'https://downloads.wordpress.org/theme/test-theme.1.0.0.zip',
        );
        $job->handle();

        $path = 'themes/test-theme/test-theme.1.0.0.zip';
        Storage::disk('s3')->assertExists($path);
        expect(Storage::disk('s3')->get($path))->toBe('bob');
    });

    it('stores asset images in S3', function () {
        $job = new DownloadAssetJob(
            AssetType::PLUGIN_SCREENSHOT,
            'test-plugin',
            'screenshot-1.png',
            'https://ps.w.org/test-plugin/assets/screenshot-1.png',
        );

        $path = 'assets/plugin/test-plugin/screenshot-1.png';
        expect($job->generateLocalPath())->toBe($path);

        $job->handle();

        Storage::disk('s3')->assertExists($path);
        expect(Storage::disk('s3')->get($path))->toBe('bob');
    });

    it('creates correct S3 URL for assets', function () {
        $job = new DownloadAssetJob(
            AssetType::PLUGIN_BANNER,
            'test-plugin',
            'banner-772x250.jpg',
            'https://ps.w.org/test-plugin/assets/banner-772x250.jpg',
        );
        $job->handle();

        $asset = Asset::first();
        $url = Storage::disk('s3')->url($asset->local_path);

        expect($url)->toEndWith('test-plugin/banner-772x250.jpg');
    });

    it('downloads and stores asset correctly on S3', function () {
        $job = new DownloadAssetJob(
            AssetType::PLUGIN,
            'test-plugin',
            'test-plugin.1.0.0.zip',
            'https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip',
        );
        $job->handle();

        Storage::disk('s3')->assertExists('plugins/test-plugin/test-plugin.1.0.0.zip');

        expect(Storage::disk('s3')->get('plugins/test-plugin/test-plugin.1.0.0.zip'))
            ->toBe('bob')
            ->and(Asset::count())->toBe(1);

        $asset = Asset::first();

        expect($asset->asset_type->value)
            ->toBe(AssetType::PLUGIN->value)
            ->and($asset->slug)->toBe('test-plugin')
            ->and($asset->version)->toBe('1.0.0')
            ->and($asset->local_path)->toBe('plugins/test-plugin/test-plugin.1.0.0.zip');
    });
})->skip(fn() => config('filesystems.default') !== 's3');
