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
        config('elasticsearch.enabled') and static::observe(ElasticSearchObserver::class);
    }
}
