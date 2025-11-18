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
     * Exists solely because Laravel IDEA can't seem to find create() without this
     *
     * @param array<string, mixed> $attributes
     */
    public static function create(array $attributes = []): static
    {
        return static::query()->create($attributes);
    }
}
