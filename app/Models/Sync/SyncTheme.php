<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
