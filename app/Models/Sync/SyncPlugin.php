<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 *
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $current_version
 * @property \Carbon\CarbonImmutable $updated
 * @property string $status
 * @property \Carbon\CarbonImmutable $pulled_at
 * @property array|null $metadata
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereCurrentVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin wherePulledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPlugin whereUpdated($value)
 * @mixin \Eloquent
 */
class SyncPlugin extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_plugins';

    protected $fillable = ['name', 'slug', 'current_version', 'status', 'metadata'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'slug' => 'string',
            'current_version' => 'string',
            'updated' => 'immutable_datetime',
            'status' => 'string',
            'pulled_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

}
