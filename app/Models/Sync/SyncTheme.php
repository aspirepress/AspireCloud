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
 * @property string $updated
 * @property string $pulled_at
 * @property array|null $metadata
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme whereCurrentVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme wherePulledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncTheme whereUpdated($value)
 * @mixin \Eloquent
 */
class SyncTheme extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_themes';

    protected $fillable = ['file_url', 'type', 'metadata', 'hash'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'file_url' => 'string',
            'type' => 'string',
            'version' => 'string',
            'metadata' => 'array',
            'created' => 'immutable_datetime',
            'processed' => 'immutable_datetime',
            'hash' => 'string',
        ];
    }

}
