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

        // DB
        Metric::query()
            ->updateOrInsert(
                ['key' => $key],
                ['value' => DB::raw("value + {$by}")]
            );
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
