<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * Strongly-typed versions of the Eloquent find() methods, with a non-overloaded API.
 * Also adds a fetchMapped() convenience method for returning associative arrays of models.
 *
 * @phpstan-require-extends Model
 *
 * Since the methods are actually on Builder, we need these simplified annotations below to suppress errors
 * @method static find(mixed $id)
 * @method static findOrFail(mixed $id)
 * @method static findMany(mixed $ids)
 */
trait HasFetchMethods
{
    public static function fetchOne(string|int|null $id): static|null
    {
        return static::find($id); // @phpstan-ignore return.type
    }

    /** @throws ModelNotFoundException<static> */
    public static function fetchOneOrFail(string|int|null $id): static
    {
        return static::findOrFail($id);  // @phpstan-ignore return.type
    }

    /**
     * @param list<int|string> $ids
     * @return Collection<int, static>
     */
    public static function fetchMany(array $ids): Collection
    {
        return static::findMany($ids);  // @phpstan-ignore return.type
    }

    /**
     * Returns a collection of models keyed by the given key.
     * If no key is provided, the model's primary key will be used.
     *
     * @param list<int|string> $ids
     * @return Collection<array-key, static>
     */
    public static function fetchMapped(array $ids, string|null $key = null): Collection
    {
        // @phpstan-ignore new.static
        $key ??= (new static)->getKeyName(); // @mago-expect analysis:trait-instantiation
        return static::fetchMany($ids)->keyBy($key);
    }
}
