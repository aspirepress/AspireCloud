<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasFetchMethods;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    // use HasUuids;  // can't be undone, so it's better to define on each subclass

    use HasFetchMethods;

    public $timestamps = false;

    protected $guarded = [];    // everything is fillable by default

    /**
     * @param array<string, mixed> $attributes
     */
    protected static function _create(array $attributes = []): static
    {
        return static::query()->create($attributes);
    }

    /**
     * Upsert a model instance.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     * @return static
     */
    protected static function _updateOrCreate(array $attributes, array $values): static
    {
        return static::query()->updateOrCreate($attributes, $values);
    }
}
