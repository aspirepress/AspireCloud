<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Metrics;

use App\Models\Metric;
use App\Services\Metrics\MetricsService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;

beforeEach(function () {
    Metric::truncate();
    config(['metrics.write_to_db_every' => 10]);
    $this->cache = new CacheRepository(new ArrayStore());
    $this->metricsService = new MetricsService($this->cache);
});

test('increment and get metrics', function () {
    $key = 'test_metric';

    // Initial value should be 0
    expect($this->metricsService->get($key))->toBe(0);

    // Increment by 5
    $this->metricsService->increment($key, 5);
    expect($this->metricsService->get($key))->toBe(5);

    // Increment by 10
    $this->metricsService->increment($key, 10);
    expect($this->metricsService->get($key))->toBe(15);

    // Increment by default (1)
    $this->metricsService->increment($key);
    expect($this->metricsService->get($key))->toBe(16);

    // Clean up
    $this->cache->forget($key);
});

test('increment and test database threshold', function () {
    $key = 'threshold_test_metric';
    $threshold = config('metrics.write_to_db_every', 100);

    // Increment below threshold
    $this->metricsService->increment($key);
    expect($this->metricsService->get($key))->toBe(1);
    $dbValue = Metric::query()->where('key', $key)->first();
    expect($dbValue)->toBe(null); // Not yet written to DB

    // Increment to reach threshold
    $this->metricsService->increment($key, $threshold);
    expect($this->metricsService->get($key))->toBe($threshold + 1);
    $dbValue = Metric::query()->where('key', $key)->first();
    expect($dbValue?->value)->toBe($threshold + 1);

    // Clean up
    $this->cache->forget($key);
});
