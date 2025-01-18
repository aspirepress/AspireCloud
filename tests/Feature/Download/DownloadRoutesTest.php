<?php

use App\Enums\AssetType;
use App\Jobs\DownloadAssetJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
    Http::fake();
});

function getDownloadJob(): DownloadAssetJob
{
    $jobs = Queue::pushed(DownloadAssetJob::class);
    expect($jobs)->toHaveCount(1);
    return $jobs->first();
}

describe('Download Routes', function () {
    it('handles WordPress core download requests', function () {
        $response = $this->get('/download/wordpress-6.4.2.zip');

        expect($response->status())->toBe(302);
        /** @noinspection PhpUndefinedMethodInspection */
        expect($response->getTargetUrl())->toBe('https://wordpress.org/wordpress-6.4.2.zip');

        $job = getDownloadJob();
        expect($job->type)
            ->toBe(AssetType::CORE)
            ->and($job->file)->toBe('wordpress-6.4.2.zip')
            ->and($job->slug)->toBe('wordpress')
            ->and($job->upstreamUrl)->toBe('https://wordpress.org/wordpress-6.4.2.zip')
            ->and($job->revision)->toBeNull();
    });

    it('handles plugin download requests', function () {
        $response = $this->get('/download/plugin/test-plugin.1.0.0.zip');

        expect($response->status())->toBe(302);
        /** @noinspection PhpUndefinedMethodInspection */
        expect($response->getTargetUrl())->toBe('https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip');

        $job = getDownloadJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN)
            ->and($job->file)->toBe('test-plugin.1.0.0.zip')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip')
            ->and($job->revision)->toBeNull();
    });

    it('handles theme download requests', function () {
        $response = $this->get('/download/theme/test-theme.1.0.0.zip');

        expect($response->status())->toBe(302);
        /** @noinspection PhpUndefinedMethodInspection */
        expect($response->getTargetUrl())->toBe('https://downloads.wordpress.org/theme/test-theme.1.0.0.zip');

        $job = getDownloadJob();
        expect($job->type)
            ->toBe(AssetType::THEME)
            ->and($job->file)->toBe('test-theme.1.0.0.zip')
            ->and($job->slug)->toBe('test-theme')
            ->and($job->upstreamUrl)->toBe('https://downloads.wordpress.org/theme/test-theme.1.0.0.zip')
            ->and($job->revision)->toBeNull();
    });

    it('handles plugin asset download requests', function () {
        $response = $this->get('/download/assets/plugin/test-plugin/head/screenshot-1.png');
        expect($response->status())->toBe(302);
        /** @noinspection PhpUndefinedMethodInspection */
        expect($response->getTargetUrl())->toBe('https://ps.w.org/test-plugin/assets/screenshot-1.png');

        $job = getDownloadJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN_SCREENSHOT)
            ->and($job->file)->toBe('screenshot-1.png')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://ps.w.org/test-plugin/assets/screenshot-1.png')
            ->and($job->revision)->toBeNull();
    });

    it('handles asset download requests with revision', function () {
        $response = $this->get('/download/assets/plugin/test-plugin/3164133/banner-1544x500.png');

        expect($response->status())->toBe(302);
        /** @noinspection PhpUndefinedMethodInspection */
        expect($response->getTargetUrl())->toBe('https://ps.w.org/test-plugin/assets/banner-1544x500.png?rev=3164133');

        $job = getDownloadJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN_BANNER)
            ->and($job->file)->toBe('banner-1544x500.png')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://ps.w.org/test-plugin/assets/banner-1544x500.png?rev=3164133')
            ->and($job->revision)->toBe('3164133');
    });
});
