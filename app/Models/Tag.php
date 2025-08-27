<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Tag extends Model
{
    use HasUuids;

    protected $table = 'tags';

    protected function casts(): array
    {
        return [
            'id'             => 'string',
            'taggable_id'    => 'string',
            'taggable_type'  => 'string',
            'name'           => 'string',
            'slug'           => 'string',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
