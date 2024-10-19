<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ThemeFile extends BaseModel {
    use HasUuids;

    protected $table = 'theme_files';

    protected $fillable = ['file_url', 'type', 'metadata', 'hash'];

    protected function casts(): array {
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
