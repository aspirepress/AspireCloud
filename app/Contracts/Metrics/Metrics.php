<?php

namespace App\Contracts\Metrics;

use Illuminate\Contracts\Cache\Repository;

interface Metrics
{
    public function __construct(Repository $cache);

    public function increment(string $key, int $by = 1): void;

    public function get(string $key): int;
}
