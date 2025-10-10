<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

require_once __DIR__.'/elasticMock.php';

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        Storage::fake('s3');
        Http::preventStrayRequests();
        $this->withoutVite();
    })
    ->in('Feature');
