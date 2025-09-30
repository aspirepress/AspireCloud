<?php

namespace App\Services\Metrics;

use App\Models\Metric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    /**
     * @param string $key
     * @param int $by
     * @return void
     */
    public static function increment(string $key, int $by = 1): void
    {
        // cache
        Cache::increment($key, $by);

        // local counter to reduce DB writes
        $counterKey = "unsynced_{$key}";
        $unsynced = Cache::increment($counterKey, $by);

        // DB
        $threshold = config('metrics.write_to_db_every', 100);
        if ($unsynced >= $threshold) {
            // update or insert
            Metric::query()
                ->updateOrInsert(
                ['key' => $key],
                ['value' => DB::raw("value + $unsynced")]
            );
            // reset local counter
            Cache::forget($counterKey);
        }
    }

    /**
     * @param string $key
     * @return int
     */
    public static function get(string $key): int
    {
        // cache
        $value = Cache::get($key);

        if ($value === null) {
            // DB
            $value = Metric::query()->where('key', $key)->value('value') ?? 0;

            // cache
            Cache::forever($key, $value);
        }

        return (int)$value;
    }
}
