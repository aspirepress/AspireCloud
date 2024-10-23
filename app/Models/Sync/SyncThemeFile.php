<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 *
 *
 * @property string $id
 * @property string $theme_id
 * @property string|null $file_url
 * @property string $type
 * @property string $version
 * @property array|null $metadata
 * @property \Carbon\CarbonImmutable $created
 * @property \Carbon\CarbonImmutable|null $processed
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncThemeFile whereVersion($value)
 * @mixin \Eloquent
 */
class SyncThemeFile extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_theme_files';

    protected $fillable = ['file_url', 'type', 'metadata', 'hash'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'file_url' => 'string',
            'type' => 'string',
            'metadata' => 'array',
            'created' => 'immutable_datetime',
            'processed' => 'immutable_datetime',
            'hash' => 'string',
        ];
    }

}
