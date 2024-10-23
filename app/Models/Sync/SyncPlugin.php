<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
