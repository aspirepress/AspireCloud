<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
    Queue::fake();
    Http::fake();
});

describe('Download Routes', function () {
    it('handles WordPress core download requests', function () {
        $response = $this->get('/download/wordpress-6.4.2.zip');
        expect($response->getStatusCode())->toBe(302);
    });

    it('rejects invalid core download requests', function () {
        // rejected by route pattern
        $this->get('/download/wordpress-.zip')->assertNotFound();
        $this->get('/download/wordpress-1.2.3.svg')->assertNotFound();
        $this->get('/download/wordpress-1.2.3.zip.zip')->assertNotFound();
        $this->get('/download/wordpress-1.2.3.zip.tar.gz')->assertNotFound();
        $this->get('/download/wordpress-1.2.3.tar.gz.zip')->assertNotFound();

        // rejected by controller validation
        $this->get('/download/wordpress-0.zip')->assertStatus(400);
        $this->get('/download/wordpress-123.zip')->assertStatus(400);
        $this->get('/download/wordpress-1.2.3.4.zip')->assertStatus(400);
        $this->get('/download/wordpress-1..zip')->assertStatus(400);
        $this->get('/download/wordpress-1.2.3..zip')->assertStatus(400);
    });

    it('handles plugin download requests', function () {
        $response = $this->get('/download/plugin/test-plugin.1.0.0.zip');
        expect($response->getStatusCode())->toBe(302);
    });

    it('rejects invalid plugin download requests', function () {
        // rejected by route pattern
        $this->get('/download/plugin/test-plugin.1.2.3.svg')->assertNotFound();
        $this->get('/download/plugin/test-plugin.1.2.3.zip.tar.gz')->assertNotFound();

        // rejected by controller validation
        // This _would_ be an issue with some plugins on .org, but we do not rewrite plugin urls without a version.
        $this->get('/download/plugin/test-plugin.zip')->assertBadRequest();

        // should perhaps be rejected, but are not currently.
        // $this->get('/download/plugin/test-plugin.1..zip')->assertBadRequest();
        // $this->get('/download/plugin/test-plugin.1.2.3.zip.zip')->assertNotFound();
        // $this->get('/download/plugin/test-plugin.1.2.3.tar.gz.zip')->assertNotFound();
        // $this->get('/download/plugin/test-plugin.1.2.3..zip')->assertBadRequest();
    });

    it('handles theme download requests', function () {
        $response = $this->get('/download/theme/test-theme.1.0.0.zip');
        expect($response->getStatusCode())->toBe(302);
    });

    it('rejects invalid theme download requests', function () {
        // rejected by route pattern
        $this->get('/download/theme/test-theme.1.2.3.svg')->assertNotFound();
        $this->get('/download/theme/test-theme.1.2.3.zip.tar.gz')->assertNotFound();

        // rejected by controller validation
        // This _would_ be an issue with some themes on .org, but we do not rewrite theme urls without a version.
        $this->get('/download/theme/test-theme.zip')->assertBadRequest();

        // should perhaps be rejected, but are not currently.
        // $this->get('/download/theme/test-theme.1..zip')->assertBadRequest();
        // $this->get('/download/theme/test-theme.1.2.3.zip.zip')->assertNotFound();
        // $this->get('/download/theme/test-theme.1.2.3.tar.gz.zip')->assertNotFound();
        // $this->get('/download/theme/test-theme.1.2.3..zip')->assertBadRequest();
    });

    it('handles plugin asset download requests', function () {
        $response = $this->get('/download/assets/plugin/test-plugin/head/screenshot-1.png');
        expect($response->getStatusCode())->toBe(302);
    });

    it('handles asset download requests with revision', function () {
        $response = $this->get('/download/assets/plugin/test-plugin/3164133/banner-1544x500.png');
        expect($response->getStatusCode())->toBe(302);
    });

    it('handles gp-icon download requests', function () {
        $this
            ->get('/download/gp-icon/plugin/test-plugin/123/test-plugin.svg')
            ->assertStatus(302);
    });

    it('handles theme screenshot download requests', function () {
        $this
            ->get('download/assets/theme/test-theme/123/screenshot-1.png')
            ->assertStatus(302);
    });
});
