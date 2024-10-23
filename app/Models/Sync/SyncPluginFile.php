<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * 
 *
 * @property string $id
 * @property string $plugin_id
 * @property string|null $file_url
 * @property string $type
 * @property string $version
 * @property array|null $metadata
 * @property \Carbon\CarbonImmutable $created
 * @property \Carbon\CarbonImmutable|null $processed
 * @property string|null $hash
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile wherePluginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncPluginFile whereVersion($value)
 * @mixin \Eloquent
 */
class SyncPluginFile extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_plugin_files';

    protected $fillable = ['file_url', 'type', 'metadata', 'hash'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
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
