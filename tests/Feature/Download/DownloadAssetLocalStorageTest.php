<?php

use App\Enums\AssetType;
use App\Jobs\DownloadAssetJob;
use App\Models\WpOrg\Asset;
use App\Services\Downloads\DownloadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
    Http::fake();
});

describe('DownloadService on local storage', function () {
    it('redirects to existing asset', function () {
        // Arrange
        $asset = Asset::factory()->create([
            'asset_type' => AssetType::PLUGIN->value,
            'slug' => 'test-plugin',
            'local_path' => 'plugins/test-plugin/test-plugin.1.0.0.zip',
        ]);

        Storage::put($asset->local_path, 'test content');

        $service = new DownloadService();

        // Act
        $response = $service->download(
            AssetType::PLUGIN,
            'test-plugin',
            'test-plugin.1.0.0.zip',
        );

        // Assert
        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getStatusCode())->toBe(302);
    });

    it('redirects to upstream and queues download of missing asset', function () {
        // Arrange
        $service = new DownloadService();

        // Act
        $response = $service->download(
            AssetType::PLUGIN,
            'test-plugin',
            'test-plugin.1.0.0.zip',
        );

        // Assert
        Queue::assertPushed(DownloadAssetJob::class, function ($job) {
            $expectedUrl = 'https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip';

            return $job->type === AssetType::PLUGIN
                && $job->slug === 'test-plugin'
                && $job->file === 'test-plugin.1.0.0.zip'
                && $job->upstreamUrl === $expectedUrl
                && $job->revision === null;
        });

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toBe('https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip');
    });

    it('redirects to temporary URL when asset exists locally', function () {
        // Arrange
        $asset = Asset::factory()->create([
            'asset_type' => AssetType::PLUGIN->value,
            'slug' => 'test-plugin',
            'local_path' => 'plugins/test-plugin.1.0.0.zip',
        ]);
        Storage::fake('s3');
        Storage::put($asset->local_path, 'test content');

        $service = new DownloadService();

        // Act
        $response = $service->download(
            AssetType::PLUGIN,
            'test-plugin',
            'test-plugin.1.0.0.zip',
        );

        // Assert
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toContain($asset->local_path);
    });

    it('downloads and stores asset', function () {
        // Arrange
        Http::fake([
            '*' => Http::response('file content'),
        ]);

        $filename = 'test-plugin.1.0.0.zip';

        $job = new DownloadAssetJob(
            AssetType::PLUGIN,
            'test-plugin',
            $filename,
            "https://downloads.wordpress.org/plugin/$filename",
        );

        // Act
        $job->handle();

        // Assert
        Storage::assertExists("plugins/test-plugin/$filename");

        expect(Storage::get("plugins/test-plugin/$filename"))->toBe('file content');

        expect(Asset::count())->toBe(1);

        $asset = Asset::first();
        expect($asset->asset_type->value)
            ->toBe(AssetType::PLUGIN->value)
            ->and($asset->slug)->toBe('test-plugin')
            ->and($asset->version)->toBe('1.0.0');
    });
})->skip(fn() => config('filesystems.default') !== 'local');
