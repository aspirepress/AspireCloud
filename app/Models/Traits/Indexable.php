<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Observers\ElasticSearchObserver;

/**
 * @method static observe(string $class)
 */
trait Indexable
{
    public static function bootIndexable(): void
    {
        // @phpstan-ignore-next-line (my disgust for Laravel's __magic knows no bounds)
        config('elasticsearch.auto_index') and static::observe(ElasticSearchObserver::class);
    }
}
