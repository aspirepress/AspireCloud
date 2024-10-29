<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Database\Factories\Sync\SyncPluginFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    /** @use HasFactory<SyncPluginFactory> */
    use HasFactory;

    protected $table = 'sync_plugins';

    /** @return HasMany<SyncPluginFile, covariant static> */
    public function files(): HasMany
    {
        return $this->hasMany(SyncPluginFile::class, 'plugin_id');
    }

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
