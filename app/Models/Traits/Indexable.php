<?php

namespace App\Models\Traits;

use App\Observers\ElasticSearchObserver;

/**
 * @method static observe(string $class)
 */
trait Indexable
{
    public static function bootIndexable(): void
    {
        // @phpstan-ignore-next-line (my hatred for Laravel's __magic has no bounds)
        config('elasticsearch.enabled') and static::observe(ElasticSearchObserver::class);
    }
}
