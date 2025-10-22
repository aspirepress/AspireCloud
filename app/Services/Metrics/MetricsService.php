<?php

namespace App\Services\Metrics;

use App\Models\Metric;
use App\Contracts\Metrics\Metrics;
use Illuminate\Contracts\Cache\Repository;

class MetricsService implements Metrics
{
    public function __construct(private Repository $cache) {}

    /**
     * @param string $key
     * @param int $by
     * @return void
     */
    public function increment(string $key, int $by = 1): void
    {
        // cache
        $this->cache->increment($key, $by);

        // local counter to reduce DB writes
        $counterKey = "unsynced_{$key}";
        $unsynced = $this->cache->increment($counterKey, $by);

        // DB
        $threshold = config('metrics.write_to_db_every', 100);
        if ($unsynced >= $threshold) {
            // update or insert
            Metric::firstOrCreate(['key' => $key], ['value' => 0])
                ->increment('value', $unsynced);
            // reset local counter
            $this->cache->forget($counterKey);
        }
    }

    /**
     * @param string $key
     * @return int
     */
    public function get(string $key): int
    {
        // cache
        $value = $this->cache->get($key);

        if ($value === null) {
            // DB
            $value = Metric::query()->where('key', $key)->value('value') ?? 0;

            // cache
            $this->cache->forever($key, $value);
        }

        return (int)$value;
    }
}
