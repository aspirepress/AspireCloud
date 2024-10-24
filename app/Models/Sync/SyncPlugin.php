<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $current_version
 * @property CarbonImmutable $updated
 * @property string $status
 * @property CarbonImmutable $pulled_at
 * @property array|null $metadata
 */
class SyncPlugin extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_plugins';

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
