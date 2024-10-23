<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
