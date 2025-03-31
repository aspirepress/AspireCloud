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

describe('Download Routes', function () {
    $getJob = function (): DownloadAssetJob {
        $jobs = Queue::pushed(DownloadAssetJob::class);
        expect($jobs)->toHaveCount(1);
        return $jobs->first();
    };

    it('handles WordPress core download requests', function () use ($getJob) {
        $response = $this->get('/download/wordpress-6.4.2.zip');

        expect($response->getStatusCode())->toBe(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::CORE)
            ->and($job->file)->toBe('wordpress-6.4.2.zip')
            ->and($job->slug)->toBe('wordpress')
            ->and($job->upstreamUrl)->toBe('https://wordpress.org/wordpress-6.4.2.zip')
            ->and($job->revision)->toBeNull();
    });

    it('handles plugin download requests', function () use ($getJob) {
        $response = $this->get('/download/plugin/test-plugin.1.0.0.zip');

        expect($response->getStatusCode())->toBe(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN)
            ->and($job->file)->toBe('test-plugin.1.0.0.zip')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://downloads.wordpress.org/plugin/test-plugin.1.0.0.zip')
            ->and($job->revision)->toBeNull();
    });

    it('handles theme download requests', function () use ($getJob) {
        $response = $this->get('/download/theme/test-theme.1.0.0.zip');

        expect($response->getStatusCode())->toBe(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::THEME)
            ->and($job->file)->toBe('test-theme.1.0.0.zip')
            ->and($job->slug)->toBe('test-theme')
            ->and($job->upstreamUrl)->toBe('https://downloads.wordpress.org/theme/test-theme.1.0.0.zip')
            ->and($job->revision)->toBeNull();
    });

    it('handles plugin asset download requests', function () use ($getJob) {
        $response = $this->get('/download/assets/plugin/test-plugin/head/screenshot-1.png');
        expect($response->getStatusCode())->toBe(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN_SCREENSHOT)
            ->and($job->file)->toBe('screenshot-1.png')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://ps.w.org/test-plugin/assets/screenshot-1.png')
            ->and($job->revision)->toBeNull();
    });

    it('handles asset download requests with revision', function () use ($getJob) {
        $response = $this->get('/download/assets/plugin/test-plugin/3164133/banner-1544x500.png');

        expect($response->getStatusCode())->toBe(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN_BANNER)
            ->and($job->file)->toBe('banner-1544x500.png')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://ps.w.org/test-plugin/assets/banner-1544x500.png?rev=3164133')
            ->and($job->revision)->toBe('3164133');
    });

    it('handles gp-icon download requests', function () use ($getJob) {
        $this
            ->get('/download/gp-icon/plugin/test-plugin/123/test-plugin.svg')
            ->assertStatus(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::PLUGIN_GP_ICON)
            ->and($job->file)->toBe('test-plugin.svg')
            ->and($job->slug)->toBe('test-plugin')
            ->and($job->upstreamUrl)->toBe('https://s.w.org/plugins/geopattern-icon/test-plugin.svg?rev=123')
            ->and($job->revision)->toBe('123');
    });

    it('handles theme screenshot download requests', function () use ($getJob) {
        $this
            ->get('download/assets/theme/test-theme/123/screenshot-1.png')
            ->assertStatus(200); // mock response

        $job = $getJob();
        expect($job->type)
            ->toBe(AssetType::THEME_SCREENSHOT)
            ->and($job->file)->toBe('screenshot-1.png')
            ->and($job->slug)->toBe('test-theme')
            ->and($job->upstreamUrl)->toBe('https://ts.w.org/wp-content/themes/test-theme/screenshot-1.png?rev=123')
            ->and($job->revision)->toBe('123');
    });
});
