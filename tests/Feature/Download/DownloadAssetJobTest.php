<?php

use App\Enums\AssetType;
use App\Jobs\DownloadAssetJob;
use App\Models\WpOrg\Asset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
    Http::fake();
});

describe('DownloadAssetJob Job', function () {
    it('extracts version correctly from different file patterns', function () {
        // Arrange
        Storage::fake('local');
        Http::fake([
            '*' => Http::response('file content'),
        ]);

        // Test core version extraction
        $coreJob = new DownloadAssetJob(
            AssetType::CORE,
            'wordpress',
            'wordpress-6.4.2.zip',
            'https://wordpress.org/wordpress-6.4.2.zip',
        );
        $coreJob->handle();

        // Test plugin version extraction
        $pluginJob = new DownloadAssetJob(
            AssetType::PLUGIN,
            'test-plugin',
            'test-plugin.2.1.0.zip',
            'https://downloads.wordpress.org/plugin/test-plugin.2.1.0.zip',
        );
        $pluginJob->handle();

        // Assert
        $coreAsset = Asset::where('asset_type', AssetType::CORE->value)->first();
        $pluginAsset = Asset::where('asset_type', AssetType::PLUGIN->value)->first();

        expect($coreAsset->version)
            ->toBe('6.4.2')
            ->and($pluginAsset->version)->toBe('2.1.0');
    });
});
