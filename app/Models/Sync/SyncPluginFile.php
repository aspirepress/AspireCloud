<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $plugin_id
 * @property string|null $file_url
 * @property string $type
 * @property string $version
 * @property array|null $metadata
 * @property CarbonImmutable $created
 * @property CarbonImmutable|null $processed
 * @property string|null $hash
 */
class SyncPluginFile extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_plugin_files';

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
